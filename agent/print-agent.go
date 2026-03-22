//go:build windows

package main

import (
	"bytes"
	"compress/gzip"
	"crypto/aes"
	"crypto/cipher"
	"crypto/rand"
	"encoding/base64"
	"encoding/hex"
	"encoding/json"
	"fmt"
	"image"
	_ "image/png"
	"io"
	"net"
	"net/http"
	"net/url"
	"os"
	"os/exec"
	"path/filepath"
	"regexp"
	"strings"
	"sync"
	"syscall"
	"time"
	"unsafe"
)

// ─────────────────────────── structs ───────────────────────────

type Config struct {
	PrinterName string `json:"printer_name"`
	SecretKey   string `json:"secret_key"`
	PaperWidth  int    `json:"paper_width"` // 58 | 80 | 110
	AutoStart   bool   `json:"auto_start"`
}

// ─────────────────────────── globals ───────────────────────────

var (
	winspoolDLL        = syscall.NewLazyDLL("winspool.drv")
	procOpenPrinterW   = winspoolDLL.NewProc("OpenPrinterW")
	procClosePrinter   = winspoolDLL.NewProc("ClosePrinter")
	procStartDocPrW    = winspoolDLL.NewProc("StartDocPrinterW")
	procEndDocPrinter  = winspoolDLL.NewProc("EndDocPrinter")
	procStartPagePr    = winspoolDLL.NewProc("StartPagePrinter")
	procEndPagePrinter = winspoolDLL.NewProc("EndPagePrinter")
	procWritePrinter   = winspoolDLL.NewProc("WritePrinter")
	procEnumPrintersW  = winspoolDLL.NewProc("EnumPrintersW")
)

type DOC_INFO_1 struct {
	DocName    *uint16
	OutputFile *uint16
	DataType   *uint16
}

// ─────────────────────────── helpers ruta ──────────────────────

func exeDir() string {
	exe, err := os.Executable()
	if err != nil {
		return "."
	}
	return filepath.Dir(exe)
}

func configPath() string {
	return filepath.Join(exeDir(), "config.json")
}

func logPath() string {
	return filepath.Join(exeDir(), "print-agent.log")
}

func logoPath() string {
	return filepath.Join(exeDir(), "logo.png")
}

// setAutoStart añade o elimina la clave de registro de inicio automático con Windows.
func setAutoStart(enable bool) {
	exe, err := os.Executable()
	if err != nil {
		logMsg("setAutoStart: " + err.Error())
		return
	}
	const regKey = `HKCU\Software\Microsoft\Windows\CurrentVersion\Run`
	var cmd *exec.Cmd
	if enable {
		cmd = exec.Command("reg", "add", regKey, "/v", "PrintAgent", "/t", "REG_SZ",
			"/d", `"`+exe+`" --background`, "/f")
	} else {
		cmd = exec.Command("reg", "delete", regKey, "/v", "PrintAgent", "/f")
	}
	cmd.SysProcAttr = &syscall.SysProcAttr{HideWindow: true}
	if err := cmd.Run(); err != nil {
		logMsg("setAutoStart: " + err.Error())
	}
}

// getAutoStart consulta si la clave de registro existe (estado real).
func getAutoStart() bool {
	cmd := exec.Command("reg", "query",
		`HKCU\Software\Microsoft\Windows\CurrentVersion\Run`, "/v", "PrintAgent")
	cmd.SysProcAttr = &syscall.SysProcAttr{HideWindow: true}
	return cmd.Run() == nil
}

// ─────────────────────────── log ───────────────────────────────

func logMsg(msg string) {
	f, err := os.OpenFile(logPath(), os.O_APPEND|os.O_CREATE|os.O_WRONLY, 0644)
	if err != nil {
		return
	}
	defer f.Close()
	fmt.Fprintln(f, msg)
}

// ─────────────────────────── config I/O ────────────────────────

func loadConfig() (Config, error) {
	var cfg Config
	data, err := os.ReadFile(configPath())
	if err != nil {
		return cfg, err
	}
	err = json.Unmarshal(data, &cfg)
	return cfg, err
}

func saveConfig(cfg Config) error {
	data, err := json.MarshalIndent(cfg, "", "  ")
	if err != nil {
		return err
	}
	return os.WriteFile(configPath(), data, 0644)
}

// ─────────────────────────── crypto ────────────────────────────

func decryptPayload(hexKey, encoded string) ([]byte, error) {
	keyBytes, err := hex.DecodeString(hexKey)
	if err != nil || len(keyBytes) != 32 {
		return nil, fmt.Errorf("clave inválida")
	}
	// base64url sin padding → bytes (RawURLEncoding maneja -_ y sin =)
	raw, err := base64.RawURLEncoding.DecodeString(encoded)
	if err != nil {
		return nil, fmt.Errorf("base64: %w", err)
	}
	if len(raw) < 12+16 {
		return nil, fmt.Errorf("payload demasiado corto")
	}
	nonce := raw[:12]
	rest := raw[12:]
	block, err := aes.NewCipher(keyBytes)
	if err != nil {
		return nil, err
	}
	gcm, err := cipher.NewGCM(block)
	if err != nil {
		return nil, err
	}
	plain, err := gcm.Open(nil, nonce, rest, nil)
	if err != nil {
		return nil, fmt.Errorf("descifrado fallido: %w", err)
	}
	return plain, nil
}

func gunzip(data []byte) ([]byte, error) {
	r, err := gzip.NewReader(bytes.NewReader(data))
	if err != nil {
		return nil, err
	}
	defer r.Close()
	return io.ReadAll(r)
}

// ─────────────────────────── winspool ──────────────────────────

func printRaw(printerName string, data []byte) error {
	namePtr, _ := syscall.UTF16PtrFromString(printerName)
	var handle uintptr
	r1, _, err := procOpenPrinterW.Call(
		uintptr(unsafe.Pointer(namePtr)),
		uintptr(unsafe.Pointer(&handle)),
		0,
	)
	if r1 == 0 {
		return fmt.Errorf("OpenPrinter: %w", err)
	}
	defer procClosePrinter.Call(handle)

	docName, _ := syscall.UTF16PtrFromString("ESC/POS")
	dataType, _ := syscall.UTF16PtrFromString("RAW")
	docInfo := DOC_INFO_1{
		DocName:  docName,
		DataType: dataType,
	}
	r1, _, err = procStartDocPrW.Call(
		handle,
		1,
		uintptr(unsafe.Pointer(&docInfo)),
	)
	if r1 == 0 {
		return fmt.Errorf("StartDocPrinter: %w", err)
	}
	defer procEndDocPrinter.Call(handle)

	r1, _, err = procStartPagePr.Call(handle)
	if r1 == 0 {
		return fmt.Errorf("StartPagePrinter: %w", err)
	}
	defer procEndPagePrinter.Call(handle)

	var written uint32
	r1, _, err = procWritePrinter.Call(
		handle,
		uintptr(unsafe.Pointer(&data[0])),
		uintptr(len(data)),
		uintptr(unsafe.Pointer(&written)),
	)
	if r1 == 0 {
		return fmt.Errorf("WritePrinter: %w", err)
	}
	return nil
}

// ─────────────────────────── EnumPrinters ──────────────────────

type PRINTER_INFO_4 struct {
	PrinterName *uint16
	ServerName  *uint16
	Attributes  uint32
}

func listPrinters() ([]string, error) {
	const PRINTER_ENUM_LOCAL = 0x00000002
	const PRINTER_ENUM_CONNECTIONS = 0x00000004
	var needed, returned uint32
	procEnumPrintersW.Call(
		PRINTER_ENUM_LOCAL|PRINTER_ENUM_CONNECTIONS,
		0,
		4,
		0,
		0,
		uintptr(unsafe.Pointer(&needed)),
		uintptr(unsafe.Pointer(&returned)),
	)
	if needed == 0 {
		return nil, nil
	}
	buf := make([]byte, needed)
	r1, _, err := procEnumPrintersW.Call(
		PRINTER_ENUM_LOCAL|PRINTER_ENUM_CONNECTIONS,
		0,
		4,
		uintptr(unsafe.Pointer(&buf[0])),
		uintptr(needed),
		uintptr(unsafe.Pointer(&needed)),
		uintptr(unsafe.Pointer(&returned)),
	)
	if r1 == 0 {
		return nil, fmt.Errorf("EnumPrinters: %w", err)
	}
	var names []string
	size := unsafe.Sizeof(PRINTER_INFO_4{})
	for i := uint32(0); i < returned; i++ {
		info := (*PRINTER_INFO_4)(unsafe.Pointer(&buf[uintptr(i)*size]))
		if info.PrinterName != nil {
			names = append(names, syscall.UTF16ToString((*[1 << 20]uint16)(unsafe.Pointer(info.PrinterName))[:]))
		}
	}
	return names, nil
}

// paperDots devuelve el ancho máximo imprimible en puntos según el mm del papel.
func paperDots(paperMM int) int {
	switch paperMM {
	case 58:
		return 384
	case 110:
		return 832
	default: // 80
		return 576
	}
}

// logoESCPOS lee C:\Pos\logo.png y genera los bytes ESC/POS GS v 0 (imagen raster).
// Escala el PNG para que quepa en el ancho del papel configurado.
// Devuelve nil si no hay logo o hay error.
func logoESCPOS(cfg Config) []byte {
	f, err := os.Open(logoPath())
	if err != nil {
		return nil
	}
	defer f.Close()

	img, _, err := image.Decode(f)
	if err != nil {
		logMsg("Logo decode: " + err.Error())
		return nil
	}

	maxW := paperDots(cfg.PaperWidth)
	bounds := img.Bounds()
	srcW := bounds.Max.X - bounds.Min.X
	srcH := bounds.Max.Y - bounds.Min.Y

	dstW := srcW
	dstH := srcH
	if srcW > maxW {
		dstH = srcH * maxW / srcW
		dstW = maxW
	}
	if dstW <= 0 || dstH <= 0 {
		return nil
	}

	bytesPerRow := (dstW + 7) / 8
	raster := make([]byte, bytesPerRow*dstH)

	for y := 0; y < dstH; y++ {
		srcY := bounds.Min.Y + y*srcH/dstH
		for x := 0; x < dstW; x++ {
			srcX := bounds.Min.X + x*srcW/dstW
			r, g, b, a := img.At(srcX, srcY).RGBA()
			// Pixel transparente → fondo blanco (no imprimir)
			if a < 0x8000 {
				continue
			}
			// Luminancia ponderada (valores RGBA son de 16 bits)
			lum := (299*r + 587*g + 114*b) / 1000
			if lum < 32768 { // oscuro → imprimir punto
				raster[y*bytesPerRow+x/8] |= 1 << uint(7-(x%8))
			}
		}
	}

	var b bytes.Buffer
	b.Write([]byte{0x1B, 0x61, 0x01})              // centrar
	b.Write([]byte{0x1D, 0x76, 0x30, 0x00})         // GS v 0, modo normal
	b.WriteByte(byte(bytesPerRow & 0xFF))
	b.WriteByte(byte(bytesPerRow >> 8))
	b.WriteByte(byte(dstH & 0xFF))
	b.WriteByte(byte(dstH >> 8))
	b.Write(raster)
	b.WriteByte(0x0A)                               // LF
	b.Write([]byte{0x1B, 0x61, 0x00})              // volver a izquierda
	logMsg(fmt.Sprintf("Logo ESC/POS: %dx%d → %d bytes", dstW, dstH, b.Len()))
	return b.Bytes()
}

// ─────────────────────────── imprimir desde URL ────────────────

// handlePrintURL descifra el payload del protocolo print:// y envía
// los bytes ESC/POS directamente a la impresora configurada.
// El servidor es responsable de generar el contenido ESC/POS completo.
func handlePrintURL(rawURL string) {
	logMsg("=== handlePrintURL recibido: " + rawURL[:min(80, len(rawURL))])
	re := regexp.MustCompile(`(?i)^print://(.+)$`)
	m := re.FindStringSubmatch(strings.TrimSpace(rawURL))
	if len(m) < 2 {
		logMsg("URL inválida: " + rawURL)
		return
	}
	payload := m[1]
	// Limpiar caracteres de control y / final que Chrome agrega al normalizar
	// la URL (trata el payload como "host" y añade slash: print://PAYLOAD/)
	payload = strings.TrimRight(payload, "/\r\n\t ")
	logMsg(fmt.Sprintf("payload len=%d primeros40=%s", len(payload), payload[:min(40, len(payload))]))

	// Eliminar prefijo de versión antigua (ej: TpV_v1_) si está presente
	if idx := strings.Index(payload, "_v"); idx > 0 && idx < 10 {
		if end := strings.Index(payload[idx:], "_"); end > 0 {
			stripped := payload[idx+end+1:]
			logMsg("Prefijo antiguo detectado, eliminando: " + payload[:idx+end+1])
			payload = stripped
		}
	}

	cfg, err := loadConfig()
	if err != nil {
		logMsg("No se pudo leer config.json: " + err.Error())
		return
	}
	if cfg.SecretKey == "" {
		logMsg("secret_key vacío en config.json")
		return
	}
	logMsg(fmt.Sprintf("key len=%d", len(cfg.SecretKey)))

	// Detectar si el payload viene percent-encoded por el navegador
	if strings.Contains(payload, "%") {
		logMsg("AVISO: payload contiene '%', decodificando URL")
		if decoded, err2 := url.QueryUnescape(payload); err2 == nil {
			payload = decoded
			logMsg("Payload tras URL-decode: " + payload[:min(40, len(payload))])
		}
	}

	compressed, err := decryptPayload(cfg.SecretKey, payload)
	if err != nil {
		logMsg("Descifrado: " + err.Error())
		return
	}
	escData, err := gunzip(compressed)
	if err != nil {
		logMsg("Descompresión: " + err.Error())
		return
	}
	logMsg(fmt.Sprintf("ESC/POS bytes: %d", len(escData)))

	// Primer byte = flag de protocolo interno PHP↔Go:
	//   0x01 → ticket: SÍ preponer logo
	//   0x00 → comanda: NO preponer logo
	//   0x02 → combinado: ticket + comanda en un solo payload
	//          formato: chr(2) + uint32_BE(len_ticket) + ticket_bytes + comanda_bytes
	//   otro → legacy sin flag (con logo)

	if len(escData) > 0 && escData[0] == 2 {
		// ── Modo combinado: imprimir ticket y luego comanda ──────────────
		if len(escData) < 5 {
			logMsg("Payload combinado demasiado corto")
			return
		}
		ticketLen := int(escData[1])<<24 | int(escData[2])<<16 | int(escData[3])<<8 | int(escData[4])
		rest := escData[5:]
		if ticketLen > len(rest) {
			logMsg(fmt.Sprintf("ticketLen=%d > rest=%d", ticketLen, len(rest)))
			return
		}
		ticketDoc  := rest[:ticketLen]
		comandaDoc := rest[ticketLen:]

		// Imprimir ticket (con logo si su flag = 0x01)
		ticketData := ticketDoc
		if len(ticketData) > 0 && (ticketData[0] == 0 || ticketData[0] == 1) {
			if ticketData[0] == 1 {
				if logoBytes := logoESCPOS(cfg); len(logoBytes) > 0 {
					ticketData = append(logoBytes, ticketData[1:]...)
				} else {
					ticketData = ticketData[1:]
				}
			} else {
				ticketData = ticketData[1:]
			}
		}
		logMsg(fmt.Sprintf("Combinado: ticket=%d bytes, comanda=%d bytes", len(ticketData), len(comandaDoc)))
		if err := printRaw(cfg.PrinterName, ticketData); err != nil {
			logMsg("Error imprimiendo ticket: " + err.Error())
		} else {
			logMsg("Ticket impreso OK")
		}

		// Imprimir comanda (con delay para que el spooler procese el ticket)
		if len(comandaDoc) > 0 {
			time.Sleep(800 * time.Millisecond)
			comandaData := comandaDoc
			if len(comandaData) > 0 && (comandaData[0] == 0 || comandaData[0] == 1) {
				if comandaData[0] == 1 {
					if logoBytes := logoESCPOS(cfg); len(logoBytes) > 0 {
						comandaData = append(logoBytes, comandaData[1:]...)
					} else {
						comandaData = comandaData[1:]
					}
				} else {
					comandaData = comandaData[1:]
				}
			}
			if err := printRaw(cfg.PrinterName, comandaData); err != nil {
				logMsg("Error imprimiendo comanda: " + err.Error())
			} else {
				logMsg("Comanda impresa OK")
			}
		}
		return
	}

	useLogo := true
	if len(escData) > 0 && (escData[0] == 0 || escData[0] == 1) {
		useLogo = escData[0] == 1
		escData = escData[1:]
		logMsg(fmt.Sprintf("flag logo=%v", useLogo))
	}

	// Preponer logo local si existe y el flag lo indica
	if useLogo {
		if logoBytes := logoESCPOS(cfg); len(logoBytes) > 0 {
			combined := make([]byte, len(logoBytes)+len(escData))
			copy(combined, logoBytes)
			copy(combined[len(logoBytes):], escData)
			escData = combined
		}
	}

	if cfg.PrinterName == "" {
		logMsg("No hay impresora configurada")
		return
	}
	if err := printRaw(cfg.PrinterName, escData); err != nil {
		logMsg("Error imprimiendo: " + err.Error())
	} else {
		logMsg("Impreso OK en " + cfg.PrinterName)
	}
}

func min(a, b int) int {
	if a < b {
		return a
	}
	return b
}

// ─────────────────────────── servidor de config ────────────────

// HTML embebido — UI de configuración
const configHTML = `<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Configurador de Impresión</title>
<style>
  *{box-sizing:border-box;margin:0;padding:0}
  body{background:#0f172a;color:#e2e8f0;font-family:'Segoe UI',sans-serif;padding:24px;min-height:100vh}
  h1{font-size:1.4rem;color:#38bdf8;margin-bottom:4px}
  .sub{font-size:.8rem;color:#64748b;margin-bottom:28px}
  .card{background:#1e293b;border:1px solid #334155;border-radius:12px;padding:20px;margin-bottom:20px}
  .card h2{font-size:.9rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.08em;margin-bottom:16px}
  label{display:block;font-size:.82rem;color:#94a3b8;margin-bottom:4px;margin-top:12px}
  label:first-child{margin-top:0}
  input{width:100%;background:#0f172a;border:1px solid #334155;border-radius:8px;color:#e2e8f0;padding:10px 12px;font-size:.88rem;outline:none}
  input:focus{border-color:#38bdf8}
  .row{display:flex;gap:8px;flex-wrap:wrap;margin-top:8px}
  .chip{background:#0f172a;border:1px solid #334155;border-radius:20px;padding:6px 14px;font-size:.8rem;cursor:pointer;transition:.15s}
  .chip:hover,.chip.active{border-color:#38bdf8;color:#38bdf8}
  .key-box{font-family:monospace;font-size:.78rem;background:#0f172a;border:1px solid #334155;border-radius:8px;padding:10px;word-break:break-all;min-height:42px;color:#4ade80}
  .key-box.empty{color:#475569}
  .btn{display:inline-block;padding:10px 20px;border-radius:8px;border:none;cursor:pointer;font-size:.88rem;font-weight:600;transition:.15s}
  .btn-primary{background:#38bdf8;color:#0f172a}
  .btn-primary:hover{background:#0ea5e9}
  .btn-outline{background:transparent;border:1px solid #38bdf8;color:#38bdf8}
  .btn-outline:hover{background:#0ea5e920}
  .btn-sm{padding:6px 14px;font-size:.8rem}
  .actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:16px}
  .toast{position:fixed;bottom:24px;right:24px;background:#1e293b;border:1px solid #334155;border-radius:10px;padding:12px 20px;font-size:.85rem;opacity:0;pointer-events:none;transition:.3s;z-index:999}
  .toast.show{opacity:1}
  .toast.ok{border-color:#4ade80;color:#4ade80}
  .toast.err{border-color:#f87171;color:#f87171}
  /* Ancho de papel */
  .paper-opts{display:flex;gap:10px;flex-wrap:wrap;margin-top:4px}
  .paper-opt{flex:1;min-width:80px;background:#0f172a;border:1px solid #334155;border-radius:10px;padding:14px 8px;text-align:center;cursor:pointer;transition:.15s}
  .paper-opt:hover{border-color:#38bdf8}
  .paper-opt.selected{border-color:#38bdf8;background:#0c2a3e}
  .paper-opt .mm{font-size:1.25rem;font-weight:700;color:#38bdf8}
  .paper-opt .label-sm{font-size:.75rem;color:#64748b;margin-top:3px}
  /* Toggle */
  .toggle-row{display:flex;align-items:center;justify-content:space-between;padding:6px 0}
  .toggle{position:relative;width:44px;height:24px;cursor:pointer;flex-shrink:0}
  .toggle input{opacity:0;width:0;height:0}
  .slider{position:absolute;inset:0;background:#334155;border-radius:12px;transition:.3s}
  .slider:before{position:absolute;content:'';height:18px;width:18px;left:3px;bottom:3px;background:#e2e8f0;border-radius:50%;transition:.3s}
  input:checked + .slider{background:#38bdf8}
  input:checked + .slider:before{transform:translateX(20px)}
  /* Logo */
  .logo-drop{border:2px dashed #334155;border-radius:10px;padding:24px 16px;text-align:center;cursor:pointer;color:#475569;font-size:.85rem;transition:.15s;margin-top:4px}
  .logo-drop:hover{border-color:#38bdf8;color:#94a3b8}
  #logo-preview{width:100%;max-height:120px;object-fit:contain;border-radius:8px;margin-top:10px;display:none;border:1px solid #334155;background:#0f172a;padding:8px}
</style>
</head>
<body>
<h1>&#x1F5A8; Configurador de Impresion ESC/POS</h1>
<p class="sub">print-agent &mdash; agente de impresion standalone</p>

<div class="card">
  <h2>Impresora</h2>
  <div class="row" id="chips"></div>
  <label>Nombre de la impresora</label>
  <input id="printer_name" type="text" placeholder="Ej: POS-80">
</div>

<div class="card">
  <h2>Ancho de papel</h2>
  <p style="font-size:.82rem;color:#64748b;margin-bottom:8px">Selecciona el ancho del rollo de tu impresora.</p>
  <div class="paper-opts">
    <div class="paper-opt" data-w="58" onclick="setPaper(58)">
      <div class="mm">58<span style="font-size:.8rem;font-weight:400">mm</span></div>
      <div class="label-sm">32 col</div>
    </div>
    <div class="paper-opt" data-w="80" onclick="setPaper(80)">
      <div class="mm">80<span style="font-size:.8rem;font-weight:400">mm</span></div>
      <div class="label-sm">42 col</div>
    </div>
    <div class="paper-opt" data-w="110" onclick="setPaper(110)">
      <div class="mm">110<span style="font-size:.8rem;font-weight:400">mm</span></div>
      <div class="label-sm">56 col</div>
    </div>
  </div>
</div>

<div class="card">
  <h2>Logo de la empresa</h2>
  <p style="font-size:.82rem;color:#64748b;margin-bottom:4px">PNG recomendado: 384px de ancho, fondo blanco.</p>
  <div class="logo-drop" id="logo-drop"
       onclick="document.getElementById('logo-file').click()"
       ondragover="event.preventDefault()" ondrop="dropLogo(event)">
    &#x1F4C2; Arrastra un PNG aqui o haz clic para seleccionar
  </div>
  <input type="file" id="logo-file" accept="image/png" style="display:none" onchange="uploadLogo(this.files[0])">
  <img id="logo-preview" alt="Logo empresa">
  <p id="logo-note" style="font-size:.78rem;color:#64748b;margin-top:8px;display:none">
    Sube el mismo archivo a <code>storage/app/logo.png</code> en el servidor para que aparezca en los tickets.
  </p>
</div>

<div class="card">
  <h2>Clave de cifrado (AES-256)</h2>
  <p style="font-size:.82rem;color:#64748b;margin-bottom:12px">
    Debe coincidir con <code>PRINTER_SECRET_KEY</code> en el servidor.
  </p>
  <div class="key-box empty" id="key-display">Sin clave guardada</div>
  <div class="actions">
    <button class="btn btn-outline btn-sm" onclick="genKey()">Generar nueva clave</button>
    <button class="btn btn-sm" id="copy-btn" onclick="copyKey()" style="display:none;background:#334155;color:#e2e8f0">Copiar</button>
  </div>
</div>

<div class="card">
  <h2>Sistema</h2>
  <div class="toggle-row">
    <div style="padding-right:12px">
      <div style="font-size:.88rem;color:#e2e8f0;margin-bottom:2px">Iniciar con Windows</div>
      <div style="font-size:.78rem;color:#64748b">El agente arrancara automaticamente al encender el equipo</div>
    </div>
    <label class="toggle">
      <input type="checkbox" id="auto-start" onchange="toggleAutoStart(this.checked)">
      <span class="slider"></span>
    </label>
  </div>
</div>

<div class="actions">
  <button class="btn btn-primary" onclick="saveConfig()">Guardar configuracion</button>
  <button class="btn btn-outline" onclick="testPrint()">Imprimir prueba</button>
</div>

<div class="toast" id="toast"></div>

<script>
let currentKey = '';
let paperWidth = 80;
let printers = [];

async function init() {
  try {
    const [cfgRes, prRes] = await Promise.all([fetch('/config'), fetch('/printers')]);
    const cfg = await cfgRes.json();
    printers = await prRes.json();
    renderChips();
    if (cfg.printer_name) document.getElementById('printer_name').value = cfg.printer_name;
    if (cfg.secret_key)   { currentKey = cfg.secret_key; showKey(currentKey); }
    setPaper(cfg.paper_width || 80);
    document.getElementById('auto-start').checked = !!cfg.auto_start;
    if (cfg.logo_exists) showLogoPreview();
  } catch(e) { toast('Error cargando config: ' + e, 'err'); }
}

function renderChips() {
  const c = document.getElementById('chips');
  c.innerHTML = '';
  (printers || []).forEach(p => {
    const d = document.createElement('div');
    d.className = 'chip';
    d.textContent = p;
    d.onclick = () => { document.getElementById('printer_name').value = p; };
    c.appendChild(d);
  });
}

function setPaper(w) {
  paperWidth = w;
  document.querySelectorAll('.paper-opt').forEach(el => {
    el.classList.toggle('selected', parseInt(el.dataset.w) === w);
  });
}

function showLogoPreview() {
  const img = document.getElementById('logo-preview');
  img.src = '/logo?t=' + Date.now();
  img.style.display = 'block';
  document.getElementById('logo-note').style.display = 'block';
}

function showKey(k) {
  const el = document.getElementById('key-display');
  el.textContent = k;
  el.classList.remove('empty');
  document.getElementById('copy-btn').style.display = 'inline-block';
  currentKey = k;
}

async function genKey() {
  const res = await fetch('/genkey', {method: 'POST'});
  const d = await res.json();
  if (d.key) showKey(d.key);
}

function copyKey() {
  navigator.clipboard.writeText(currentKey).then(() => toast('Clave copiada', 'ok'));
}

async function saveConfig() {
  const body = {
    printer_name: document.getElementById('printer_name').value.trim(),
    secret_key:   currentKey,
    paper_width:  paperWidth,
    auto_start:   document.getElementById('auto-start').checked
  };
  const res = await fetch('/save', {method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify(body)});
  const d = await res.json();
  toast(d.message || 'Guardado', d.ok ? 'ok' : 'err');
}

async function toggleAutoStart(val) {
  const body = {
    printer_name: document.getElementById('printer_name').value.trim(),
    secret_key:   currentKey,
    paper_width:  paperWidth,
    auto_start:   val
  };
  const res = await fetch('/save', {method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify(body)});
  const d = await res.json();
  toast(val ? 'Inicio automatico activado' : 'Inicio automatico desactivado', d.ok ? 'ok' : 'err');
}

async function testPrint() {
  const body = {
    printer:     document.getElementById('printer_name').value.trim(),
    paper_width: paperWidth
  };
  const res = await fetch('/test', {method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify(body)});
  const d = await res.json();
  toast(d.message || 'Listo', d.ok ? 'ok' : 'err');
}

async function uploadLogo(file) {
  if (!file) return;
  const fd = new FormData();
  fd.append('logo', file);
  const res = await fetch('/upload-logo', {method: 'POST', body: fd});
  const d = await res.json();
  toast(d.message || 'Logo guardado', d.ok ? 'ok' : 'err');
  if (d.ok) showLogoPreview();
}

function dropLogo(ev) {
  ev.preventDefault();
  const file = ev.dataTransfer.files[0];
  if (file && file.type === 'image/png') uploadLogo(file);
  else toast('Solo se acepta PNG', 'err');
}

function toast(msg, type) {
  const el = document.getElementById('toast');
  el.textContent = msg;
  el.className = 'toast show ' + (type || 'ok');
  setTimeout(() => el.classList.remove('show'), 3000);
}

init();
</script>
</body>
</html>`

// ─────────────────────────── HTTP handlers ─────────────────────

var mu sync.Mutex

func hConfig(w http.ResponseWriter, r *http.Request) {
	if r.Method == http.MethodGet && r.URL.Path == "/" {
		w.Header().Set("Content-Type", "text/html; charset=utf-8")
		io.WriteString(w, configHTML)
		return
	}
	http.NotFound(w, r)
}

func hGetConfig(w http.ResponseWriter, r *http.Request) {
	mu.Lock()
	cfg, err := loadConfig()
	mu.Unlock()
	if err != nil {
		cfg = Config{}
	}
	// Refleja el estado real del registro para auto_start
	cfg.AutoStart = getAutoStart()
	if cfg.PaperWidth == 0 {
		cfg.PaperWidth = 80
	}
	type cfgResponse struct {
		Config
		LogoExists bool `json:"logo_exists"`
	}
	_, statErr := os.Stat(logoPath())
	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(cfgResponse{Config: cfg, LogoExists: statErr == nil})
}

func hPrinters(w http.ResponseWriter, r *http.Request) {
	names, _ := listPrinters()
	if names == nil {
		names = []string{}
	}
	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(names)
}

func hGenKey(w http.ResponseWriter, r *http.Request) {
	b := make([]byte, 32)
	rand.Read(b)
	key := hex.EncodeToString(b)
	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(map[string]string{"key": key})
}

func hSave(w http.ResponseWriter, r *http.Request) {
	var body Config
	if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
		w.WriteHeader(400)
		json.NewEncoder(w).Encode(map[string]interface{}{"ok": false, "message": "JSON inv\u00e1lido"})
		return
	}
	if body.PaperWidth == 0 {
		body.PaperWidth = 80
	}
	mu.Lock()
	err := saveConfig(body)
	mu.Unlock()
	if err != nil {
		json.NewEncoder(w).Encode(map[string]interface{}{"ok": false, "message": err.Error()})
		return
	}
	setAutoStart(body.AutoStart)
	json.NewEncoder(w).Encode(map[string]interface{}{"ok": true, "message": "Configuraci\u00f3n guardada"})
}

func hTest(w http.ResponseWriter, r *http.Request) {
	var body struct {
		Printer    string `json:"printer"`
		PaperWidth int    `json:"paper_width"`
	}
	if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
		w.WriteHeader(400)
		json.NewEncoder(w).Encode(map[string]interface{}{"ok": false, "message": "JSON inv\u00e1lido"})
		return
	}
	if body.Printer == "" {
		json.NewEncoder(w).Encode(map[string]interface{}{"ok": false, "message": "Nombre de impresora vac\u00edo"})
		return
	}

	cols := 42
	widthMM := 80
	switch body.PaperWidth {
	case 58:
		cols, widthMM = 32, 58
	case 110:
		cols, widthMM = 56, 110
	}
	sep := strings.Repeat("-", cols)

	var b bytes.Buffer
	b.Write([]byte{0x1B, 0x40})              // ESC @ — init
	b.Write([]byte{0x1B, 0x61, 0x01})        // centrar
	b.Write([]byte{0x1D, 0x21, 0x11})        // doble tamano
	b.WriteString("PRUEBA DE IMPRESION\n")
	b.Write([]byte{0x1D, 0x21, 0x00})        // normal
	b.WriteString("print-agent\n")
	b.Write([]byte{0x1B, 0x61, 0x00})        // izquierda
	b.WriteString(sep + "\n")
	b.WriteString(fmt.Sprintf("Impresora: %s\n", body.Printer))
	b.WriteString(fmt.Sprintf("Papel:     %dmm (%d col)\n", widthMM, cols))
	b.WriteString("Estado:    OK\n")
	b.Write([]byte{0x1B, 0x64, 0x04})        // avance 4 lineas
	b.Write([]byte{0x1D, 0x56, 0x41, 0x00})  // corte parcial

	if err := printRaw(body.Printer, b.Bytes()); err != nil {
		json.NewEncoder(w).Encode(map[string]interface{}{"ok": false, "message": err.Error()})
		return
	}
	json.NewEncoder(w).Encode(map[string]interface{}{"ok": true, "message": "Impreso en " + body.Printer})
}

// ─────────────────────────── logo upload/serve ─────────────────

func hUploadLogo(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		w.WriteHeader(405)
		return
	}
	r.ParseMultipartForm(5 << 20) // 5 MB max
	file, _, err := r.FormFile("logo")
	if err != nil {
		w.Header().Set("Content-Type", "application/json")
		json.NewEncoder(w).Encode(map[string]interface{}{"ok": false, "message": "Archivo inv\u00e1lido: " + err.Error()})
		return
	}
	defer file.Close()
	data, err := io.ReadAll(file)
	if err != nil {
		w.Header().Set("Content-Type", "application/json")
		json.NewEncoder(w).Encode(map[string]interface{}{"ok": false, "message": err.Error()})
		return
	}
	// Verificar firma PNG: 89 50 4E 47
	if len(data) < 4 || data[0] != 0x89 || data[1] != 0x50 || data[2] != 0x4E || data[3] != 0x47 {
		w.Header().Set("Content-Type", "application/json")
		json.NewEncoder(w).Encode(map[string]interface{}{"ok": false, "message": "El archivo no es un PNG v\u00e1lido"})
		return
	}
	if err := os.WriteFile(logoPath(), data, 0644); err != nil {
		w.Header().Set("Content-Type", "application/json")
		json.NewEncoder(w).Encode(map[string]interface{}{"ok": false, "message": err.Error()})
		return
	}
	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(map[string]interface{}{"ok": true, "message": "Logo guardado correctamente"})
}

func hLogoImg(w http.ResponseWriter, r *http.Request) {
	f, err := os.Open(logoPath())
	if err != nil {
		http.NotFound(w, r)
		return
	}
	defer f.Close()
	w.Header().Set("Content-Type", "image/png")
	w.Header().Set("Cache-Control", "no-cache")
	io.Copy(w, f)
}

// ─────────────────────────── servidor ──────────────────────────

func startConfigServer(openBrowser bool) {
	mux := http.NewServeMux()
	mux.HandleFunc("/", hConfig)
	mux.HandleFunc("/config", hGetConfig)
	mux.HandleFunc("/printers", hPrinters)
	mux.HandleFunc("/genkey", hGenKey)
	mux.HandleFunc("/save", hSave)
	mux.HandleFunc("/test", hTest)
	mux.HandleFunc("/upload-logo", hUploadLogo)
	mux.HandleFunc("/logo", hLogoImg)

	ln, err := net.Listen("tcp", ":9876")
	if err != nil {
		// ya está corriendo — abrir browser si corresponde
		if openBrowser {
			exec.Command("cmd", "/c", "start", "http://localhost:9876").Start()
		}
		return
	}

	if openBrowser {
		go func() {
			exec.Command("cmd", "/c", "start", "http://localhost:9876").Start()
		}()
	}
	http.Serve(ln, mux)
}

// ─────────────────────────── main ──────────────────────────────

func main() {
	args := os.Args[1:]

	if len(args) == 0 || args[0] == "--config" {
		startConfigServer(true)
		return
	}

	// Modo background: servidor de config silencioso (auto-inicio con Windows)
	if args[0] == "--background" {
		startConfigServer(false)
		return
	}

	// Modo impresión: primer argumento es la URL print://...
	handlePrintURL(args[0])
}
