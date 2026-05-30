# Japanesetravel Design Contract

## 1. Design Goal
Mendesain UI yang terasa **human-made, editorial, calm, dan professional** untuk konteks:
- Japanese destination discovery
- curated souvenir commerce

Target utama:
- konsisten di **light + dark theme**
- menjaga alur kritis (cart, checkout, payment, admin actions) tetap jelas
- meningkatkan karakter visual tanpa mengorbankan keterbacaan dan kecepatan task

## 2. Product Positioning
Project ini diposisikan sebagai:
- **destination discovery platform with reviews**
- **souvenir e-commerce**

Bukan fitur aktif:
- ticketing/booking pembelian tiket destinasi **belum aktif**
- ticketing hanya disebut sebagai **future roadmap**

## 3. Design Principles
1. Editorial over template.
2. Restrained over flashy.
3. Image-first but readable.
4. Commerce clarity before decoration.
5. Admin utility before branding flourish.
6. Accessible contrast by default.
7. Light/dark parity (bukan dark sebagai afterthought).
8. Honest UX copy, no overclaim.

## 4. Anti-AI Design Rules
Larangan eksplisit:
- Jangan gunakan purple-blue generic gradient sebagai identitas utama.
- Jangan gunakan glassmorphism berlebihan.
- Jangan gunakan neon glow.
- Jangan jadikan emoji sebagai sistem icon utama.
- Jangan gunakan copywriting bombastis.
- Jangan gunakan pola card grid monoton untuk semua section.
- Jangan gunakan hero ala SaaS template generik.
- Jangan overuse `rounded-3xl` + shadow besar di semua elemen.
- Jangan buat layout terlalu simetris tanpa ritme editorial.

## 5. Brand Personality
Harus terasa:
- calm
- curated
- warm
- cultural
- trustworthy
- travel editorial
- boutique commerce

Hindari:
- childish anime theme
- over-Japanese cliché
- corporate SaaS tone
- luxury hotel overkill

## 6. Light Theme Tokens
- `--bg-paper: #F8F7F4` → background utama halaman public.
- `--surface: #FFFFFF` → card/form/table utama.
- `--surface-muted: #F1EEE8` → section pemisah / secondary surfaces.
- `--border: #E7E3DC` → divider, input border, card outline.
- `--text-primary: #1F2937` → heading dan body utama.
- `--text-secondary: #5F6B7A` → metadata, helper text.
- `--accent-vermilion: #B53A3A` → CTA utama, action penting.
- `--accent-tea: #2F5D50` → positive/info editorial accent.
- `--accent-indigo: #334E68` → navigational accent / secondary action.

Pemakaian:
- Accent maksimal 1 dominan per layar.
- Prioritaskan neutral surfaces; accent dipakai untuk hierarchy, bukan dekorasi.

## 7. Dark Theme Tokens
- `--bg-ink: #0E1116` → background global.
- `--surface: #161B22` → card utama.
- `--surface-muted: #1F2630` → section/toolbar secondary.
- `--border: #2A333D` → separator dan field boundary.
- `--text-primary: #E5E7EB` → teks utama.
- `--text-secondary: #AAB4C0` → teks sekunder.
- `--accent-vermilion-soft: #D46A6A` → primary action.
- `--accent-tea-soft: #4B7A6E` → positive state / success nuance.
- `--accent-warm-gold: #C7A76C` → subtle highlight, bukan CTA utama.

Aturan:
- Hindari saturasi tinggi di dark mode.
- Tidak ada neon atau glow kuat.
- Contrast minimum WCAG AA untuk text UI inti.

## 8. Typography
- Heading/editorial titles: **Fraunces**
- Body/UI: **Manrope**

Aturan:
- Heading besar hanya untuk hero dan section opener penting.
- Jangan overuse display font di table/form/admin.
- Body line-height nyaman (`~1.5–1.7`).
- Admin lebih utilitarian: heading tegas, body ringkas.

## 9. Layout Rules
- Gunakan container width konsisten per context.
- Spacing scale: `4 / 8 / 12 / 16 / 24 / 32 / 48 / 64`.
- Variasikan ritme section (full-bleed image, split content, dense list), jangan grid seragam terus-menerus.
- Public: boleh editorial dan atmosferik.
- Commerce: cepat, jelas, minim distraksi.
- Admin: dense namun terbaca, fokus pada task completion.

## 10. Component Rules
### Buttons
- Primary: aksi utama halaman, contrast tinggi.
- Secondary: aksi pendukung.
- Ghost/Text: aksi minor.
- Destructive: warna terpisah, selalu jelas risiko.

### Cards
- Editorial card: image-led + narrative snippet.
- Commerce card: product info-first + price/stock/action jelas.
- Admin panel card: data density, minim ornament.

### Forms
- Input/select/textarea height konsisten.
- Label selalu visible.
- Error state kontras dan spesifik.

### Status & Feedback
- Badge/status semantic konsisten (`pending/paid/failed/...`).
- Alert flash style seragam lintas user/admin.

### Tables
- Header tegas, row spacing stabil, hover subtle.
- Jangan over-style; prioritaskan scanability.

### Pagination
- Ukuran tap target nyaman.
- Active state jelas di light & dark.

### Modal/Dropdown
- Backdrop ringan, hierarchy jelas.
- Keyboard close support.

### Navigation
- Public nav: destination + shop + account + cart cepat ditemukan.
- Admin nav: task group jelas (dashboard/orders/places/souvenirs/inventory).

### Footer
- Informasi ringkas, tidak jadi “marketing wall”.

## 11. Image Treatment
- Place image: rasio `4:3` atau `3:2`.
- Product image: rasio `1:1`.
- Overlay jangan terlalu berat.
- Di dark mode boleh ada dim subtle agar teks terbaca.
- Hindari image random yang tidak kontekstual dengan Jepang/travel/souvenir.

## 12. Page-by-Page Direction
### Homepage
- Editorial discovery first, commerce as intentional secondary CTA.
- Variasi blok konten agar tidak card-grid terus.

### Place listing/detail
- Fokus narasi destinasi, metadata operasional, review social proof.
- Detail page harus terasa seperti editorial article, bukan toko.

### Review section
- Form ringkas, readable, no visual clutter.
- Feedback submit/error jelas dan manusiawi.

### Shop/souvenir listing
- Filter + sorting mudah di-scan.
- Price, stock, add-to-cart jadi hierarchy utama.

### Cart
- Quantity, subtotal, dan error stok paling menonjol.
- Checkout CTA selalu terlihat.

### Checkout
- Pemilihan provider simpel.
- Informasi konsekuensi pembayaran jelas (redirect/external provider).

### Orders index/show
- Timeline status order/payment mudah dipahami.
- Retry payment tampil hanya saat valid.

### Login/register/verify email
- Satu visual language dengan public/commercial UI.
- Hindari style legacy campur-campur.

### Admin login
- Utilitarian + trustable, tidak flashy.

### Admin dashboard
- KPI hierarchy jelas.
- Grafik mendukung keputusan, bukan dekorasi.

### Admin places/souvenirs
- CRUD forms cepat dibaca, validasi jelas.

### Admin inventory/restock
- Prioritaskan low stock visibility + safe restock action.

### Admin orders
- Filtering kuat + status clarity + safe destructive cues.

## 13. Admin Design Philosophy
Admin area harus terasa:
- modern
- professional
- operational
- data-driven

Prinsip:
- Fungsi operasional lebih penting daripada dekorasi.
- Keputusan cepat lebih penting daripada visual gimmick.
- Public = editorial travel, Commerce = clean shopping, **Admin = modern operational backoffice**.
- Jangan klaim “real-time” jika belum ada mekanisme teknis yang mendukung.

## 14. Admin Dashboard Data Visualization
Dashboard admin harus menampilkan **data nyata dari database** (bukan chart dekoratif atau angka dummy).

Minimal struktur informasi:
- current operational metrics / latest database snapshot
- trend panel (orders/revenue)
- status distribution (order/payment)
- alert panel (low stock / failed payment / pending workload)
- recent activity table

Jika data query tertentu belum tersedia:
- jangan paksakan widget kosong palsu
- tampilkan state “data unavailable yet” yang jujur

## 15. Admin KPI Cards
KPI prioritas (tampilkan sesuai ketersediaan query aktual):
- Total orders
- Pending orders
- Completed orders
- Revenue / total sales (jika tersedia)
- Low stock count
- Failed payments
- Recent reviews/activity (jika tersedia)

Aturan KPI:
- Angka harus berasal dari agregasi database nyata.
- Tidak semua metric wajib tampil jika backend belum menyuplai data.
- Hindari icon random di setiap KPI; gunakan seperlunya.
- Hindari gradient mencolok di seluruh KPI cards.

## 16. Admin Charts
Jenis chart yang direkomendasikan:
- **Line chart**: tren order/revenue per periode.
- **Bar chart**: top products/categories (jika data tersedia).
- **Donut/Pie**: hanya untuk proporsi status (order/payment), penggunaan terbatas.

Prioritas konten:
- low stock list lebih penting daripada chart dekoratif
- payment status distribution
- order status distribution
- recent orders table
- stock alerts
- review/activity summary (jika data tersedia)

Anti-noise:
- Maksimal 2–3 chart inti per dashboard utama.
- Jika informasi lebih mudah dipahami via table/list, utamakan table/list.

## 17. Admin Tables
Table adalah komponen inti operasional.

Aturan:
- Scanability prioritas: kolom penting di kiri, action di kanan.
- Status badge wajib konsisten dengan order/payment state machine.
- Numeric alignment konsisten (amount, count, date).
- Row density efisien, tapi tetap punya whitespace cukup.
- Sticky filter bar disarankan jika tabel panjang.

## 18. Admin Filters
Filter harus:
- mudah ditemukan
- mudah di-reset
- memberikan feedback aktif (apa yang sedang difilter)

Filter minimum:
- status order
- status payment
- date range
- keyword (order id/user/email)

UX filter:
- hindari form filter terlalu panjang pada mobile
- gunakan urutan dari yang paling sering dipakai

## 19. Admin Empty / Loading / Error States
Wajib ada state untuk:
- empty data
- loading data
- error fetch data

Aturan:
- jangan biarkan panel kosong tanpa konteks
- gunakan copy yang ringkas, spesifik, operasional
- sediakan action recovery yang relevan (retry/reset filter)

## 20. Admin Light/Dark Chart Treatment
Light/dark chart style:
- warna muted, bukan neon
- grid line subtle
- axis label terbaca
- legend ringkas
- tooltip jelas (angka, label, waktu)
- chart tidak boleh jadi pusat perhatian berlebihan

Token handling:
- line/bar colors konsisten dengan palette admin
- dark mode gunakan kontras cukup tanpa saturasi ekstrem

## 21. Real-time / Data Freshness Rules
Istilah yang boleh dipakai tanpa mekanisme live:
- current operational metrics
- latest database snapshot
- dashboard-ready metrics

Istilah “real-time” hanya boleh dipakai jika benar-benar ada:
- polling
- websocket
- Livewire push/pull update
- SSE
- mekanisme auto-refresh serupa

Jika belum ada, jangan klaim real-time.

## 22. Admin UX Rules
- Admin harus task-oriented.
- Action utama harus jelas (approve/process/update/restock).
- Destructive action harus berhati-hati (visual + confirmation).
- Table scanability adalah prioritas.
- Filter/search harus cepat dipahami.
- Status badge harus konsisten dengan state machine backend.

## 23. Admin Anti-AI Rules
Larangan khusus admin:
- Jangan pakai chart hanya sebagai hiasan.
- Jangan pakai gradient dashboard cards berlebihan.
- Jangan pakai icon random di tiap KPI.
- Jangan tampilkan angka palsu.
- Jangan membuat dashboard seperti crypto/SaaS template generik.

## 24. UX Rules
- Checkout harus jelas, minim dekorasi berlebihan.
- Payment status harus bisa dipahami sekilas.
- Cart error copy harus manusiawi dan actionable.
- Review form harus jelas ekspektasinya.
- Admin action priority harus jelas.
- Destructive action wajib hati-hati (warna + confirm cue).
- Flash message harus konsisten posisi dan style.

## 25. Accessibility Rules
- Pastikan contrast light/dark memadai.
- Focus ring jelas di semua interactive elements.
- Keyboard navigation wajib untuk nav/form/dropdown.
- Error state tidak hanya warna, sertakan teks.
- Label form tidak boleh hilang.
- Status tidak boleh hanya dibedakan oleh hue.

## 26. Implementation Phases
1. **Phase 1**: design tokens + component consolidation.
2. **Phase 2**: auth/profile pages cleanup.
3. **Phase 3**: public editorial pages.
4. **Phase 4**: commerce pages.
5. **Phase 5**: admin UI polish.
6. **Phase 6**: dark mode QA + responsive polish.

## 27. Regression Safety Checklist
- Jangan hapus `@csrf`.
- Jangan hapus `@method`.
- Jangan ubah `name` input form yang dipakai backend validation.
- Jangan ubah route action form.
- Jangan ubah Alpine state tanpa cek interaksi.
- Jangan ubah form payment/order/admin tanpa verifikasi test.
- Setelah tiap phase: jalankan `npm run build` dan `php artisan test`.

## 28. Copywriting Tone
- Natural
- Specific
- Not hype
- Hindari cliché “magical journey”
- Terdengar seperti travel editorial manusia, bukan template marketing
- Bilingual support (ID/EN) harus dipertimbangkan pada microcopy kunci

## 29. Final Do / Don’t
| Do | Don’t |
|---|---|
| Pakai hierarchy konten yang jelas | Menumpuk dekorasi tanpa fungsi |
| Jaga konsistensi token light/dark | Campur banyak palet aksen di satu layar |
| Utamakan readability & task clarity | Mengorbankan UX untuk efek visual |
| Gunakan foto kontekstual dan rasio stabil | Pakai image random/stock tidak relevan |
| Sederhanakan komponen ke satu sistem | Membiarkan dua gaya komponen konflik |
| Gunakan copy natural dan spesifik | Copy bombastis, hype, atau overclaim |
| Pastikan status/payment/admin terbaca cepat | Menyembunyikan informasi penting di ornament |
| Review tiap perubahan dengan test/build | Mengubah UI kritikal tanpa regression check |
