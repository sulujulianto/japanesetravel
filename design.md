# Japanesetravel Design Contract

## 1. Design Goal
Mendesain UI yang terasa **human-made, editorial, calm, dan professional** untuk konteks:
- Japanese destination discovery
- curated souvenir commerce

Target utama:
- terasa seperti marketplace travel/commerce yang jelas, bukan template SaaS
- photography-led: foto destinasi dan produk menjadi pembawa karakter utama
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

## 3. Marketplace Reference Adaptation
Referensi marketplace modern dipakai sebagai **inspiration pattern**, bukan cloning brand, layout, warna, font, copy, atau visual identity.

Yang diadaptasi:
- marketplace clarity
- photography-led browsing
- white/warm paper canvas
- single primary accent
- soft rounded components
- low elevation / almost flat surfaces
- modest typography
- compact search/filter UX
- dense but clean listing cards

Yang tidak boleh terjadi:
- UI terasa seperti clone marketplace lain
- menggunakan nama/style reference sebagai label desain final
- memakai font proprietary seperti Airbnb Cereal
- memakai warna yang terlalu identik dengan brand lain seperti `#ff385c`
- mengubah JapanTravel menjadi generic accommodation marketplace

## 4. What to Borrow
- Foto adalah sinyal utama: tempat dan produk harus terlihat nyata, terang, dan relevan.
- Listing harus cepat di-scan: nama, lokasi/kategori, harga/stok/rating, dan aksi utama jelas.
- Cards boleh soft, tetapi tetap compact dan tidak terlalu dekoratif.
- Filter/search harus mudah dipakai tanpa terasa seperti form enterprise.
- Surface mostly flat; shadow hanya tipis untuk hierarchy.
- CTA utama memakai satu accent konsisten, bukan palet ramai.
- Typography harus modest: kuat untuk hierarchy, tidak oversized.

## 5. What Not to Copy
- Jangan copy layout, spacing, warna, copy, atau brand behavior secara literal.
- Jangan pakai Airbnb Cereal VF atau font proprietary lain.
- Jangan pakai `#ff385c` sebagai primary brand color.
- Jangan gunakan icon/emoji dekoratif sebagai identitas.
- Jangan membuat hero/listing terasa seperti rental/hotel booking jika fitur ticketing belum aktif.
- Jangan klaim fitur booking/ticketing atau real-time bila belum didukung backend.
- Jangan menggunakan generic blue CTA, purple-blue AI gradient, neon glow, heavy glassmorphism, atau SaaS hero template.

## 6. JapanTravel Adaptation Rules
JapanTravel harus terasa seperti:
- destination discovery + review platform
- souvenir marketplace yang curated
- Japanese travel editorial yang calm
- operational admin backoffice yang modern

Aturan adaptasi:
- Public pages: image-first, editorial, warm, dan tidak terlalu marketing.
- Shop pages: product-photo-first, dense, clean, dan cepat untuk add-to-cart.
- Auth pages: calm, trustable, tidak memakai emoji atau split SaaS template tanpa visual kuat.
- Admin pages: data nyata, task-oriented, tidak dekoratif.
- Ticketing tidak boleh dipresentasikan sebagai fitur aktif.

## 7. Design Principles
1. Editorial over template.
2. Restrained over flashy.
3. Marketplace clarity before decoration.
4. Photography-led but readable.
5. Commerce clarity before decoration.
6. Admin utility before branding flourish.
7. Accessible contrast by default.
8. Light/dark parity (bukan dark sebagai afterthought).
9. Honest UX copy, no overclaim.

## 8. Anti-AI Design Rules
Larangan eksplisit:
- Jangan gunakan purple-blue generic gradient sebagai identitas utama.
- Jangan gunakan glassmorphism berlebihan.
- Jangan gunakan neon glow.
- Jangan jadikan emoji sebagai sistem icon utama.
- Jangan pakai emoji sebagai dekorasi auth/admin/nav utama.
- Jangan gunakan copywriting bombastis.
- Jangan gunakan pola card grid monoton untuk semua section.
- Jangan gunakan hero ala SaaS template generik.
- Jangan overuse `rounded-3xl` + shadow besar di semua elemen.
- Jangan buat layout terlalu simetris tanpa ritme editorial.
- Jangan gunakan generic blue CTA sebagai default.

## 9. Brand Personality
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

## 10. Light Theme Tokens
- `--canvas: #FFFFFF` → marketplace canvas utama, khusus listing/search/cart yang butuh clarity.
- `--canvas-paper: #FAF9F6` → warm paper background untuk editorial sections.
- `--surface: #FFFFFF` → card, form, table, dropdown.
- `--surface-muted: #F6F4EF` → secondary surface yang tetap clean.
- `--hairline: #DDDDDD` atau `#E7E3DC` → divider, card outline, input border.
- `--ink: #222222` atau `#1F2937` → heading/body utama.
- `--muted: #6A6A6A` → metadata, helper, secondary nav.
- `--primary-accent: #B53A3A` atau `#B33A3A` → CTA utama muted vermilion.
- `--primary-active: #8F2E2E` → hover/active state.
- `--primary-disabled: #E7B8B8` → disabled/tint state.

Pemakaian:
- Hanya satu primary accent dominan per layar.
- CTA utama tidak memakai generic blue.
- Neutral canvas harus dominan agar foto destinasi/produk menjadi karakter utama.
- Border harus hairline dan shadow minimal.

## 11. Dark Theme Tokens
- `--canvas: #0E1116` → background global.
- `--surface: #161B22` → card, form, table, dropdown.
- `--surface-muted: #1F2630` → secondary surface / admin toolbar.
- `--hairline: #2A333D` → separator dan input border.
- `--ink: #E5E7EB` → text utama.
- `--muted: #AAB4C0` → text sekunder.
- `--primary-accent: #D96B6B` → soft vermilion CTA.
- `--primary-active: #E18484` → hover/active state.

Aturan:
- Dark mode bukan invert warna.
- Surface layering harus jelas: canvas, surface, muted surface.
- Foto boleh diberi dim subtle agar UI tetap readable.
- Hindari saturasi tinggi di dark mode.
- Tidak ada neon atau glow kuat.
- Contrast minimum WCAG AA untuk text UI inti.

## 12. Typography
- Body/UI: **Manrope**
- Editorial heading/logo only: **Fraunces**
- Jangan pakai Airbnb Cereal atau font proprietary lain.

Aturan:
- Heading tidak terlalu besar dan tidak terlalu berat.
- Heading besar hanya untuk hero dan section opener penting.
- Jangan overuse display font di table/form/admin.
- Body line-height nyaman (`~1.5–1.7`).
- Admin lebih utilitarian: heading tegas, body ringkas.
- Hindari copy bombastis dan headline yang terasa generated.

## 13. Layout Rules
- Gunakan container width konsisten per context.
- Spacing scale: `4 / 8 / 12 / 16 / 24 / 32 / 48 / 64`.
- Variasikan ritme section (full-bleed image, split content, dense list), jangan grid seragam terus-menerus.
- Public: editorial dan photography-led, bukan SaaS hero.
- Commerce: cepat, jelas, dense, minim distraksi.
- Admin: dense namun terbaca, fokus pada task completion.
- Search/filter harus terlihat seperti marketplace controls, bukan form report berat.

## 14. Component Rules
### Buttons
- Primary: aksi utama halaman, contrast tinggi.
- Secondary: aksi pendukung.
- Ghost/Text: aksi minor.
- Destructive: warna terpisah, selalu jelas risiko.
- Radius default `8px`; pill hanya untuk nav/search/filter/chip konteks marketplace.
- Primary memakai muted vermilion, bukan blue/purple.

### Cards
- Place card: image-led, metadata lokasi/kategori/rating jelas.
- Souvenir card: product photo `1:1`, price/stock/action jelas.
- Card radius sekitar `14px`, shadow minimal.
- Jangan pakai card nested.
- Admin panel card: data density, minim ornament.

### Forms
- Input/select/textarea height konsisten.
- Label selalu visible.
- Error state kontras dan spesifik.
- No blue focus glow.
- Focus ring memakai primary accent secara subtle.

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
- Jangan pakai emoji decoration sebagai nav identity.

### Footer
- Informasi ringkas, tidak jadi “marketing wall”.

## 15. Search / Filter Direction
- Search harus menjadi entry point jelas untuk destination dan shop.
- Gunakan pill atau compact segmented controls jika cocok.
- Active filter harus terlihat dan mudah di-reset.
- Filter marketplace harus dekat dengan listing, bukan tersembunyi terlalu dalam.
- Sorting harus ringkas: latest, price low/high, availability.
- Mobile filter boleh collapsible, tetapi state aktif tetap jelas.

## 16. Image Treatment
- Place image: rasio `4:3` atau `3:2`.
- Product image: rasio `1:1`.
- Overlay jangan terlalu berat.
- Di dark mode boleh ada dim subtle agar teks terbaca.
- Hindari image random yang tidak kontekstual dengan Jepang/travel/souvenir.
- Foto harus terasa sebagai konten utama, bukan background blur dekoratif.

## 17. Page-by-Page Direction
### Homepage
- Destination photography first, commerce as intentional secondary CTA.
- Variasi blok konten agar tidak card-grid terus.
- Hero tidak memakai SaaS split template; gunakan foto/visual destinasi jika ada asset kuat.

### Place listing/detail
- Fokus narasi destinasi, metadata operasional, review social proof.
- Detail page harus terasa seperti editorial article, bukan toko.
- Listing place harus marketplace-clear: image, title, location/category, rating/review cue.

### Review section
- Form ringkas, readable, no visual clutter.
- Feedback submit/error jelas dan manusiawi.

### Shop/souvenir listing
- Filter + sorting mudah di-scan.
- Price, stock, add-to-cart jadi hierarchy utama.
- Card harus product-photo-first, compact, dan tidak terlalu shadow-heavy.

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
- Jangan pakai emoji.
- Jangan pakai tombol biru generik.
- Jangan pakai split panel yang terasa SaaS template jika tidak ada visual kuat.
- Copy harus pendek, natural, dan tidak bombastis.
- Auth harus calm, trustable, dan satu bahasa dengan marketplace.

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

## 18. Auth Direction
Auth pages harus terasa calm, trustable, dan menyatu dengan marketplace.

Aturan:
- Jangan pakai emoji di heading, CTA, atau brand lockup utama.
- Jangan pakai tombol biru generik.
- Jangan pakai copy hype atau sapaan berlebihan.
- Jangan pakai split panel ala SaaS template jika tidak ada foto/visual kuat yang relevan.
- Jangan pakai background gradient sebagai pengganti identitas visual.
- Form harus jelas: label visible, error terbaca, CTA utama tunggal.
- Login/register harus terasa seperti bagian dari JapanTravel, bukan Breeze default.

Copy yang dihindari:
- "Selamat Datang Kembali!"
- "Buat Akun Baru"
- "Jelajahi Keajaiban Jepang tanpa ribet"
- "Petualangan Menantimu!"

Copy yang disarankan:
- "Masuk ke Japan Travel"
- "Lanjutkan eksplorasi destinasi dan pesanan Anda."
- "Buat akun"
- "Simpan destinasi, tulis ulasan, dan kelola pesanan oleh-oleh Anda."
- "Temukan destinasi Jepang dan oleh-oleh pilihan."

## 19. Admin Design Philosophy
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

## 20. Admin Dashboard Data Visualization
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

## 21. Admin KPI Cards
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

## 22. Admin Charts
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

## 23. Admin Tables
Table adalah komponen inti operasional.

Aturan:
- Scanability prioritas: kolom penting di kiri, action di kanan.
- Status badge wajib konsisten dengan order/payment state machine.
- Numeric alignment konsisten (amount, count, date).
- Row density efisien, tapi tetap punya whitespace cukup.
- Sticky filter bar disarankan jika tabel panjang.

## 24. Admin Filters
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

## 25. Admin Empty / Loading / Error States
Wajib ada state untuk:
- empty data
- loading data
- error fetch data

Aturan:
- jangan biarkan panel kosong tanpa konteks
- gunakan copy yang ringkas, spesifik, operasional
- sediakan action recovery yang relevan (retry/reset filter)

## 26. Admin Light/Dark Chart Treatment
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

## 27. Real-time / Data Freshness Rules
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

## 28. Admin UX Rules
- Admin harus task-oriented.
- Action utama harus jelas (approve/process/update/restock).
- Destructive action harus berhati-hati (visual + confirmation).
- Table scanability adalah prioritas.
- Filter/search harus cepat dipahami.
- Status badge harus konsisten dengan state machine backend.

## 29. Admin Anti-AI Rules
Larangan khusus admin:
- Jangan pakai chart hanya sebagai hiasan.
- Jangan pakai gradient dashboard cards berlebihan.
- Jangan pakai icon random di tiap KPI.
- Jangan tampilkan angka palsu.
- Jangan membuat dashboard seperti crypto/SaaS template generik.

## 30. UX Rules
- Checkout harus jelas, minim dekorasi berlebihan.
- Payment status harus bisa dipahami sekilas.
- Cart error copy harus manusiawi dan actionable.
- Review form harus jelas ekspektasinya.
- Admin action priority harus jelas.
- Destructive action wajib hati-hati (warna + confirm cue).
- Flash message harus konsisten posisi dan style.

## 31. Accessibility Rules
- Pastikan contrast light/dark memadai.
- Focus ring jelas di semua interactive elements.
- Keyboard navigation wajib untuk nav/form/dropdown.
- Error state tidak hanya warna, sertakan teks.
- Label form tidak boleh hilang.
- Status tidak boleh hanya dibedakan oleh hue.

## 32. Implementation Phases
1. **Phase 1**: design tokens + component consolidation.
2. **Phase 2**: auth/profile pages cleanup.
3. **Phase 3**: public editorial pages.
4. **Phase 4**: commerce pages.
5. **Phase 5**: admin UI polish.
6. **Phase 6**: dark mode QA + responsive polish.

## 33. Regression Safety Checklist
- Jangan hapus `@csrf`.
- Jangan hapus `@method`.
- Jangan ubah `name` input form yang dipakai backend validation.
- Jangan ubah route action form.
- Jangan ubah Alpine state tanpa cek interaksi.
- Jangan ubah form payment/order/admin tanpa verifikasi test.
- Setelah tiap phase: jalankan `npm run build` dan `php artisan test`.

## 34. Copywriting Tone
- Natural
- Specific
- Not hype
- Hindari cliché “magical journey”
- Terdengar seperti travel editorial manusia, bukan template marketing
- Bilingual support (ID/EN) harus dipertimbangkan pada microcopy kunci
- Hindari headline "keajaiban", "petualangan menanti", atau sapaan dengan emoji.
- Gunakan copy pendek yang menjelaskan aksi nyata user.

## 35. Final Do / Don’t
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
| Adaptasi prinsip marketplace secara jujur | Menyalin brand/reference secara literal |
