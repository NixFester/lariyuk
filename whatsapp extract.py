import re
import random
import string
from datetime import datetime

# ── Category config ──────────────────────────────────────────────────────────
CATEGORY_MAP = {
    # keywords (lowercase) → (event_category_id, subtotal)
    "5k pelajar":  (47, 150000),
    "5k umum":     (48, 150000),
    "10k umum":    (49, 200000),
    "10k master":  (50, 200000),
}

# ── Gender guessing ───────────────────────────────────────────────────────────
FEMALE_ENDINGS = ("wati", "yani", "ani", "eni", "ini", "uni", "ati",
                  "ah", "a", "i", "ita", "ina", "ela", "era", "nia",
                  "nty", "nti", "nty")
FEMALE_NAMES = {
    "sari", "dewi", "putri", "sri", "yuni", "tuti", "rita", "dina",
    "lina", "mia",  "nia",   "ria", "rini", "widi", "ayu", "novi",
    "noven", "cahyani", "indah", "fitri", "nurul", "ida", "lastri",
    "wulan", "bulan", "laras", "sekar", "mawar", "melati", "anisa",
    "annisa", "nisa", "zahra", "fatimah", "siti", "nur", "noor",
    "yayuk", "ratih", "retno", "endah", "suci", "cantika",
}

MALE_NAMES = {
    "budi", "adi", "agus", "bambang", "dedi", "eko", "fajar", "galih",
    "hendra", "irwan", "joko", "kukuh", "luthfi", "made", "nanda",
    "oki", "pandu", "raka", "sigit", "tono", "umar", "vino", "wahyu",
    "xander", "yogi", "zaki", "arif", "arif", "bagas", "bayu", "dimas",
    "doni", "fadli", "febri", "gilang", "hafiz", "ilham", "ivan",
    "jefri", "kevin", "lukman", "mario", "novan", "oscar", "putra",
    "radit", "rendi", "sandy", "tanto", "taufik", "ucup", "valdi",
    "wawan", "yoga", "yusuf", "anto", "didik", "hadi", "iwan",
    "koko", "nanang", "rudi", "sandi", "susanto", "teguh", "widodo",
}

def guess_gender(full_name: str) -> str:
    """Return 'P' (perempuan) or 'L' (laki-laki) based on name heuristics."""
    name_lower = full_name.lower()
    parts = name_lower.split()

    # Explicit male name wins immediately
    for part in parts:
        if part in MALE_NAMES:
            return "L"

    # Explicit female name
    for part in parts:
        if part in FEMALE_NAMES:
            return "P"

    # Check endings of each word
    for part in parts:
        for ending in FEMALE_ENDINGS:
            if part.endswith(ending) and len(part) > len(ending):
                return "P"

    return "L"  # default to male if uncertain


# ── Phone normalisation ───────────────────────────────────────────────────────
def normalize_phone(phone: str) -> str:
    """Convert 08xxx → 628xxx, strip non-digits otherwise."""
    digits = re.sub(r"\D", "", phone)
    if digits.startswith("0"):
        digits = "62" + digits[1:]
    return digits


# ── Invoice generator ─────────────────────────────────────────────────────────
def generate_invoice(timestamp: datetime | None = None) -> str:
    """INV + 4 random digits + DDMMYYYY of today (or given timestamp)."""
    dt = timestamp or datetime.now()
    rand_part = "".join(random.choices(string.digits, k=4))
    date_part = dt.strftime("%d%m%Y")
    return f"INV{rand_part}{date_part}"


# ── Category parser ───────────────────────────────────────────────────────────
def parse_category(raw: str):
    """
    Map raw category string → (event_category_id, subtotal).
    Accepts formats like '5k Pelajar', '10K Umum', '10k master', etc.
    """
    normalised = raw.lower().strip()
    # strip leading/trailing garbage
    normalised = re.sub(r"\s+", " ", normalised)

    for key, value in CATEGORY_MAP.items():
        if key in normalised:
            return value

    raise ValueError(f"Unknown category: '{raw}'. "
                     f"Valid keys: {list(CATEGORY_MAP.keys())}")


# ── Date parser ───────────────────────────────────────────────────────────────
def parse_date(raw: str) -> str:
    """Parse DD-MM-YYYY or D-M-YYYY → YYYY-MM-DD."""
    raw = raw.strip().replace("/", "-")
    for fmt in ("%d-%m-%Y", "%-d-%-m-%Y"):
        try:
            return datetime.strptime(raw, fmt).strftime("%Y-%m-%d")
        except ValueError:
            pass
    # fallback: try each component manually
    parts = raw.split("-")
    if len(parts) == 3:
        d, m, y = parts
        return f"{y.zfill(4)}-{m.zfill(2)}-{d.zfill(2)}"
    raise ValueError(f"Cannot parse date: '{raw}'")


# ── Main parser ───────────────────────────────────────────────────────────────
def parse_wa_registration(text: str) -> dict:
    """Extract fields from a WhatsApp registration block."""

    def find(pattern, default=""):
        m = re.search(pattern, text, re.IGNORECASE | re.MULTILINE)
        return m.group(1).strip() if m else default

    nama      = find(r"Nama Lengkap\s*:\s*(.+)")
    nickname  = find(r"Nama BIB\s*:\s*(.+)")
    email     = find(r"E-?mail\s+Aktif\s*:\s*(.+)")
    phone_raw = find(r"No\.?\s*WhatsApp\s*:\s*(.+)")
    address   = find(r"Alamat\s*:\s*(.+)")        # not used in SQL but parsed
    size      = find(r"Size\s*:\s*(.+)")
    category  = find(r"Categor(?:y|i)\s*:\s*(.+)")
    blood     = find(r"Gol(?:ongan)?\s*[Dd]arah\s*:\s*(.+)")
    dob_raw   = find(r"Tgl\s+[Ll]ahir\s*:\s*(.+)")
    emergency = find(r"Kontak\s+[Dd]arurat\s*:\s*(.+)")
    history   = find(r"[Pp]unya\s+[Rr]iwayat\s+[Ss]akit\s*:\s*(.+)")

    # Size normalisation: "XS- sport" → "XS-sport"
    size = re.sub(r"\s*-\s*", "-", size)

    # Category → DB ids
    cat_id, subtotal = parse_category(category)

    # Phone
    phone = normalize_phone(phone_raw)

    # Emergency contact: might be just a number or "name: number"
    emerg_name_match = re.match(r"^([^\d]+?)\s*[:\-]\s*([\d\s\-+]+)$", emergency)
    if emerg_name_match:
        kontak_darurat_nama = emerg_name_match.group(1).strip()
        kontak_darurat_hp   = normalize_phone(emerg_name_match.group(2))
    else:
        kontak_darurat_nama = "-"
        kontak_darurat_hp   = normalize_phone(emergency)

    # Gender
    gender = guess_gender(nama)

    # DOB
    dob = parse_date(dob_raw) if dob_raw else ""

    return {
        "event_id":             4,
        "event_category_id":    cat_id,
        "nama_peserta":         nama,
        "nickname":             nickname,
        "email":                email,
        "phone":                phone,
        "tanggal_lahir":        dob,
        "jenis_kelamin":        gender,
        "ukuran_kaos":          size,
        "golongan_darah":       blood,
        "kontak_darurat_nama":  kontak_darurat_nama,
        "kontak_darurat_hp":    kontak_darurat_hp,
        "riwayat_sakit":        history or "-",
        "subtotal":             subtotal,
        "admin_fee":            0,
        "total":                subtotal,
        "invoice_number":       generate_invoice(),
    }


# ── SQL builder ───────────────────────────────────────────────────────────────
def to_sql(d: dict) -> str:
    def q(v):
        if v is None:
            return "NULL"
        return "'" + str(v).replace("'", "''") + "'"

    return (
        "INSERT INTO `registrations` "
        "(`id`, `event_id`, `event_category_id`, `no_ktp`, `nama_peserta`, "
        "`nickname`, `email`, `phone`, `tanggal_lahir`, `jenis_kelamin`, "
        "`ukuran_kaos`, `golongan_darah`, `kontak_darurat_nama`, "
        "`kontak_darurat_hp`, `invoice_number`, `payment_method`, "
        "`ipaymu_transaction_id`, `ipaymu_paid_at`, `midtrans_transaction_id`, "
        "`midtrans_paid_at`, `payment_status`, `qris_displayed_at`, "
        "`whatsapp_confirmed_at`, `payment_verified_at`, `subtotal`, "
        "`admin_fee`, `total`, `is_early_bird`, `ticket_email_sent`, "
        "`created_at`, `updated_at`) VALUES ("
        f"NULL, "
        f"{q(d['event_id'])}, "
        f"{q(d['event_category_id'])}, "
        f"'', "                                    # no_ktp
        f"{q(d['nama_peserta'])}, "
        f"{q(d['nickname'])}, "
        f"{q(d['email'])}, "
        f"{q(d['phone'])}, "
        f"{q(d['tanggal_lahir'])}, "
        f"{q(d['jenis_kelamin'])}, "
        f"{q(d['ukuran_kaos'])}, "
        f"{q(d['golongan_darah'])}, "
        f"{q(d['kontak_darurat_nama'])}, "
        f"{q(d['kontak_darurat_hp'])}, "
        f"{q(d['invoice_number'])}, "
        f"NULL, NULL, NULL, NULL, NULL, "          # payment_method … midtrans_paid_at
        f"'paid', "
        f"NULL, NULL, NULL, "                      # qris … payment_verified_at
        f"{q(d['subtotal'])}, "
        f"{q(d['admin_fee'])}, "
        f"{q(d['total'])}, "
        f"'0', '0', "                              # is_early_bird, ticket_email_sent
        f"NULL, NULL);"                            # created_at, updated_at
    )


# ── Entry point ───────────────────────────────────────────────────────────────
def convert(raw_text: str) -> str:
    """Full pipeline: raw WA text → SQL INSERT string."""
    data = parse_wa_registration(raw_text)
    return to_sql(data)


# ── Demo / test ───────────────────────────────────────────────────────────────
if __name__ == "__main__":
    samples = [
        """
[08:12, 10/04/2026] +62 812-2787-9784: Mohon maaf untuk yang belum mendapatkan email registrasi ulang
Kami lakukan pendataan lewat wa kirim 
Nama Lengkap : Noventy Cahyani 
Nama BIB : Ventyy
E-mail Aktif : noventyy7@gmail.com
No. WhatsApp :089504767587
Alamat : Singamerta RT 04/RW 01, kabupaten.Banjarnegara, kecamatan.Sigaluh
Size : XS- sport
Category : 5k Pelajar
Gol darah : B
Tgl lahir : 22-11-2008
Kontak darurat :085291057858
Punya riwayat sakit:-
""",
        """
Nama Lengkap : Budi Santoso
Nama BIB : BudiRun
E-mail Aktif : budi@example.com
No. WhatsApp : 081234567890
Alamat : Jl. Merdeka No.1, Semarang
Size : L-sport
Category : 10k Umum
Gol darah : O
Tgl lahir : 15-06-1990
Kontak darurat : 082198765432
Punya riwayat sakit: Asma ringan
""",
    ]

    for i, sample in enumerate(samples, 1):
        print(f"─── Sample {i} ───────────────────────────────────────────")
        sql = convert(sample)
        print(sql)
        print()

    from wa_to_sql import convert

    sql = convert(watext.txt)
    print(sql)
