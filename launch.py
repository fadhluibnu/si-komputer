import tkinter as tk
from tkinter import ttk, scrolledtext, font, messagebox
import subprocess
import threading
import os
import socket
import webbrowser

# --- Konfigurasi ---
APP_TITLE = "SI KOMPUTER LAUNCHER"
PHP_EXE = os.path.join("bin", "php", "php.exe")
NODE_DIR = os.path.join("bin", "nodejs")
DB_BROWSER_EXE = os.path.join("tools", "db-browser", "DB Browser for SQLite.exe")
DB_SQLITE_FILE = os.path.join("sistem", "database", "database.sqlite")
LARAVEL_DIR = "sistem"
SETUP_LOCK_FILE = ".setup_complete"

class App(tk.Tk):
    def __init__(self):
        super().__init__()

        self.title(APP_TITLE)
        self.geometry("700x550")
        self.resizable(False, False)
        self.protocol("WM_DELETE_WINDOW", self.on_closing)

        # Proses server yang berjalan
        self.php_process = None
        self.npm_process = None

        # Style
        self.style = ttk.Style(self)
        self.style.theme_use('clam')
        self.configure_styles()

        # UI
        self.create_widgets()
        self.check_initial_state()

    def configure_styles(self):
        """Mendefinisikan palet warna baru untuk UI."""
        self.style.configure('TFrame', background='#f1f3f5')
        self.style.configure('TLabel', background='#f1f3f5', font=('Segoe UI', 10))
        self.style.configure('Header.TLabel', font=('Segoe UI', 14, 'bold'))
        self.style.configure('Link.TLabel', foreground="#007bff", font=('Segoe UI', 10, 'underline'))
        
        # Default Button (Install)
        self.style.configure('TButton', padding=6, relief="flat", background="#6c757d", foreground="white", font=('Segoe UI', 10, 'bold'))
        self.style.map('TButton', background=[('active', '#5a6268')])

        # Success Button (Start)
        self.style.configure('Success.TButton', background="#28a745", foreground="white")
        self.style.map('Success.TButton', background=[('active', '#218838')])

        # Danger Button (Stop)
        self.style.configure('Danger.TButton', background="#dc3545", foreground="white")
        self.style.map('Danger.TButton', background=[('active', '#c82333')])

        # Primary Button (Database)
        self.style.configure('Primary.TButton', background="#007bff", foreground="white")
        self.style.map('Primary.TButton', background=[('active', '#0069d9')])

    def create_widgets(self):
        main_frame = ttk.Frame(self, padding="20")
        main_frame.pack(expand=True, fill=tk.BOTH)

        # --- Bagian Instalasi ---
        install_frame = ttk.LabelFrame(main_frame, text=" Instalasi (Hanya Sekali Jalan) ", padding="15")
        install_frame.pack(fill=tk.X, pady=(0, 20))

        self.btn_deps = ttk.Button(install_frame, text="1. Install Dependencies", command=lambda: self.run_script_in_thread("dependencies.bat"))
        self.btn_deps.pack(fill=tk.X, pady=5)

        self.btn_install = ttk.Button(install_frame, text="2. Install Proyek Laravel", command=lambda: self.run_script_in_thread("install.bat", self.mark_setup_complete))
        self.btn_install.pack(fill=tk.X, pady=5)

        # --- Bagian Operasional ---
        ops_frame = ttk.LabelFrame(main_frame, text=" Operasional ", padding="15")
        ops_frame.pack(fill=tk.X, pady=(0, 20))

        self.btn_start = ttk.Button(ops_frame, text="Start Servers", style="Success.TButton", command=self.start_servers)
        self.btn_start.pack(side=tk.LEFT, expand=True, fill=tk.X, padx=(0, 5))

        self.btn_stop = ttk.Button(ops_frame, text="Stop Servers", style="Danger.TButton", command=self.stop_servers, state=tk.DISABLED)
        self.btn_stop.pack(side=tk.LEFT, expand=True, fill=tk.X, padx=(5, 0))

        self.btn_db = ttk.Button(ops_frame, text="Lihat Database", style="Primary.TButton", command=self.open_database)
        self.btn_db.pack(side=tk.LEFT, expand=True, fill=tk.X, padx=(15, 0))

        # --- Info Server ---
        info_frame = ttk.Frame(main_frame)
        info_frame.pack(fill=tk.X, pady=10)
        
        self.server_status_label = ttk.Label(info_frame, text="Server Status: Berhenti")
        self.server_status_label.pack(anchor=tk.W)
        
        self.ip_address = self.get_local_ip()
        self.domain_url = f"http://{self.ip_address}:8000"
        
        link_label = ttk.Label(info_frame, text="Alamat Akses:")
        link_label.pack(anchor=tk.W, pady=(10,0))
        
        self.domain_link = ttk.Label(info_frame, text=self.domain_url, style="Link.TLabel", cursor="hand2")
        self.domain_link.pack(anchor=tk.W)
        self.domain_link.bind("<Button-1>", lambda e: webbrowser.open_new(self.domain_url))

        # --- Output Log ---
        log_frame = ttk.LabelFrame(main_frame, text=" Log Output ", padding="10")
        log_frame.pack(expand=True, fill=tk.BOTH)
        
        self.log_area = scrolledtext.ScrolledText(log_frame, wrap=tk.WORD, state=tk.DISABLED, font=('Consolas', 9), bg="#2b2b2b", fg="#d3d3d3")
        self.log_area.pack(expand=True, fill=tk.BOTH)

    def log(self, message):
        """Menulis pesan ke log area dengan aman dari thread manapun."""
        if not message:
            return
        self.log_area.config(state=tk.NORMAL)
        self.log_area.insert(tk.END, message + "\n")
        self.log_area.config(state=tk.DISABLED)
        self.log_area.see(tk.END)
        self.update_idletasks()

    def run_command(self, command, cwd, callback=None):
        """Worker function untuk menjalankan command dan menampilkan output secara real-time."""
        try:
            self.after(0, self.log, f"Menjalankan: {' '.join(command)}")
            process = subprocess.Popen(
                command,
                cwd=cwd,
                stdout=subprocess.PIPE,
                stderr=subprocess.STDOUT,
                text=True,
                encoding='utf-8',
                errors='replace',
                creationflags=subprocess.CREATE_NO_WINDOW
            )

            # Membaca output baris per baris secara real-time
            for line in iter(process.stdout.readline, ''):
                self.after(0, self.log, line.strip())

            process.stdout.close()
            return_code = process.wait()

            if return_code == 0:
                self.after(0, self.log, f"\nPROSES SELESAI DENGAN SUKSES.")
                if callback:
                    self.after(0, callback)
            else:
                self.after(0, self.log, f"\nPROSES GAGAL DENGAN KODE: {return_code}")
                self.after(0, lambda: messagebox.showerror("Error", f"Proses gagal dengan kode: {return_code}."))

        except FileNotFoundError as e:
            self.after(0, self.handle_error, f"File tidak ditemukan: {e.filename}")
        except Exception as e:
            self.after(0, self.handle_error, f"Error tidak terduga: {str(e)}")
    
    def handle_error(self, error_msg):
        """Menampilkan pesan error dengan aman."""
        self.log(f"ERROR: {error_msg}")
        messagebox.showerror("Error", error_msg)

    def run_script_in_thread(self, script_name, callback=None):
        """Menyiapkan dan memulai thread untuk menjalankan skrip .bat."""
        self.log(f"--- Menjalankan {script_name} ---")
        if not os.path.exists(script_name):
            self.handle_error(f"Script tidak ditemukan: {script_name}")
            return

        command = ['cmd', '/c', script_name]
        thread = threading.Thread(target=self.run_command, args=(command, ".", callback))
        thread.daemon = True
        thread.start()

    def get_local_ip(self):
        try:
            s = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
            s.settimeout(0.1)
            s.connect(("8.8.8.8", 80))
            ip = s.getsockname()[0]
            s.close()
            return ip
        except Exception:
            return "127.0.0.1"

    def start_servers(self):
        self.log("--- Memulai Server ---")
        
        env = os.environ.copy()
        env["PATH"] = os.path.abspath(NODE_DIR) + os.pathsep + env["PATH"]

        php_command = [PHP_EXE, "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
        self.php_process = subprocess.Popen(php_command, cwd=LARAVEL_DIR, stdout=subprocess.PIPE, stderr=subprocess.STDOUT, text=True, creationflags=subprocess.CREATE_NO_WINDOW)
        threading.Thread(target=self.stream_output, args=(self.php_process, "PHP"), daemon=True).start()
        self.log("Laravel server dimulai...")

        npm_command = ["npm", "run", "dev", "--", "--host"]
        self.npm_process = subprocess.Popen(npm_command, cwd=LARAVEL_DIR, stdout=subprocess.PIPE, stderr=subprocess.STDOUT, text=True, shell=True, env=env, creationflags=subprocess.CREATE_NO_WINDOW)
        threading.Thread(target=self.stream_output, args=(self.npm_process, "NPM"), daemon=True).start()
        self.log("Vite server dimulai...")

        self.btn_start.config(state=tk.DISABLED)
        self.btn_stop.config(state=tk.NORMAL)
        self.server_status_label.config(text="Server Status: Berjalan")

    def stop_servers(self):
        self.log("--- Menghentikan Server ---")
        if self.npm_process:
            subprocess.call(['taskkill', '/F', '/T', '/PID', str(self.npm_process.pid)], creationflags=subprocess.CREATE_NO_WINDOW)
            self.npm_process = None
            self.log("Vite server dihentikan.")
        if self.php_process:
            self.php_process.terminate()
            self.php_process = None
            self.log("Laravel server dihentikan.")
        
        self.btn_start.config(state=tk.NORMAL)
        self.btn_stop.config(state=tk.DISABLED)
        self.server_status_label.config(text="Server Status: Berhenti")

    def stream_output(self, process, name):
        for line in iter(process.stdout.readline, ''):
            self.log(f"[{name}] {line.strip()}")

    def open_database(self):
        if not os.path.exists(DB_BROWSER_EXE):
            messagebox.showerror("Error", "DB Browser for SQLite tidak ditemukan.")
            return
        if not os.path.exists(DB_SQLITE_FILE):
            messagebox.showerror("Error", "File database.sqlite tidak ditemukan.")
            return
        self.log("--- Membuka Database ---")
        subprocess.Popen([DB_BROWSER_EXE, DB_SQLITE_FILE])

    def mark_setup_complete(self):
        with open(SETUP_LOCK_FILE, "w") as f:
            f.write("complete")
        self.check_initial_state()

    def check_initial_state(self):
        if os.path.exists(SETUP_LOCK_FILE):
            self.log("Setup sudah pernah dijalankan. Tombol instalasi dinonaktifkan.")
            self.btn_deps.config(state=tk.DISABLED)
            self.btn_install.config(state=tk.DISABLED)
            self.btn_start.config(state=tk.NORMAL)
            self.btn_db.config(state=tk.NORMAL)
        else:
            self.log("Setup belum dijalankan. Silakan jalankan instalasi.")
            self.btn_start.config(state=tk.DISABLED)
            self.btn_db.config(state=tk.DISABLED)

    def on_closing(self):
        if self.php_process or self.npm_process:
            if messagebox.askokcancel("Keluar", "Server masih berjalan. Apakah Anda yakin ingin keluar?"):
                self.stop_servers()
                self.destroy()
        else:
            self.destroy()

if __name__ == "__main__":
    app = App()
    app.mainloop()
