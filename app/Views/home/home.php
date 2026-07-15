<?= $this->extend('layout') ?>

<?= $this->section('content') ?>

<style>
    /* ── Google Font ── */
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

    /* ── Reset for this page ── */
    .cv-page * { font-family: 'Inter', sans-serif; box-sizing: border-box; }

    /* ── Hero ── */
    .cv-hero {
        position: relative;
        width: 100%;
        min-height: 58vh;
        overflow: hidden;
        display: flex;
        align-items: flex-end;
    }
    .cv-hero img.cv-hero-bg {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
        transition: transform 8s ease;
    }
    .cv-hero:hover img.cv-hero-bg { transform: scale(1.04); }
    .cv-hero-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(
            to top,
            rgba(18, 32, 18, 0.92) 0%,
            rgba(18, 32, 18, 0.55) 55%,
            rgba(18, 32, 18, 0.15) 100%
        );
    }
    .cv-hero-content {
        position: relative;
        z-index: 2;
        padding: 2.5rem 2.5rem 2.8rem;
        width: 100%;
    }
    .cv-hero-badge {
        display: inline-block;
        background: rgba(180, 155, 60, 0.18);
        border: 1px solid rgba(180, 155, 60, 0.55);
        color: #e0c56a;
        font-size: 0.72rem;
        font-weight: 600;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        padding: 0.28rem 0.85rem;
        border-radius: 50px;
        margin-bottom: 0.75rem;
    }
    .cv-hero-name {
        font-size: clamp(1.8rem, 4vw, 3rem);
        font-weight: 800;
        color: #fff;
        line-height: 1.15;
        margin: 0 0 0.5rem;
        letter-spacing: -0.02em;
    }
    .cv-hero-subtitle {
        font-size: clamp(0.9rem, 2vw, 1.1rem);
        color: rgba(255,255,255,0.72);
        font-weight: 400;
        margin-bottom: 1.4rem;
    }
    .cv-hero-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 0.55rem;
    }
    .cv-hero-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        background: rgba(255,255,255,0.09);
        backdrop-filter: blur(6px);
        border: 1px solid rgba(255,255,255,0.18);
        color: rgba(255,255,255,0.85);
        font-size: 0.78rem;
        font-weight: 500;
        padding: 0.3rem 0.75rem;
        border-radius: 50px;
        text-decoration: none;
        transition: background 0.2s, border-color 0.2s;
    }
    .cv-hero-chip:hover {
        background: rgba(255,255,255,0.18);
        border-color: rgba(255,255,255,0.35);
        color: #fff;
    }
    .cv-hero-chip i { font-size: 0.7rem; opacity: 0.75; }

    /* ── Section Cards ── */
    .cv-section {
        background: #fff;
        border-radius: 14px;
        border: 1px solid rgba(0,0,0,0.06);
        box-shadow: 0 2px 12px rgba(0,0,0,0.06), 0 1px 3px rgba(0,0,0,0.04);
        padding: 1.8rem 2rem;
        margin-bottom: 1.25rem;
        transition: box-shadow 0.25s ease, transform 0.25s ease;
    }
    .cv-section:hover {
        box-shadow: 0 6px 24px rgba(0,0,0,0.10), 0 2px 6px rgba(0,0,0,0.06);
        transform: translateY(-2px);
    }
    .cv-section-header {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        margin-bottom: 1.2rem;
        padding-bottom: 0.9rem;
        border-bottom: 1px solid #f0f0f0;
    }
    .cv-section-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: linear-gradient(135deg, #1e4d20, #2d6e30);
        color: #e0c56a;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.95rem;
        flex-shrink: 0;
    }
    .cv-section-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1a1a1a;
        margin: 0;
        letter-spacing: -0.01em;
    }

    /* ── Summary text ── */
    .cv-summary-text {
        color: #4a4a4a;
        line-height: 1.8;
        font-size: 0.97rem;
        font-weight: 400;
        margin: 0;
    }

    /* ── Skills ── */
    .cv-skill-group { margin-bottom: 1.1rem; }
    .cv-skill-group:last-child { margin-bottom: 0; }
    .cv-skill-label {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #888;
        margin-bottom: 0.55rem;
    }
    .cv-skill-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
    }
    .cv-skill-tag {
        font-size: 0.8rem;
        font-weight: 500;
        padding: 0.28rem 0.75rem;
        border-radius: 50px;
        border: 1px solid;
        transition: all 0.2s;
    }
    .cv-skill-tag.primary {
        background: #edf7ee;
        color: #1e5c22;
        border-color: #b5d9b8;
    }
    .cv-skill-tag.primary:hover {
        background: #1e4d20;
        color: #e0c56a;
        border-color: #1e4d20;
    }
    .cv-skill-tag.secondary {
        background: #fdf8e8;
        color: #7a6010;
        border-color: #e8d580;
    }
    .cv-skill-tag.secondary:hover {
        background: #c9a82a;
        color: #fff;
        border-color: #c9a82a;
    }
    .cv-skill-tag.neutral {
        background: #f4f4f5;
        color: #444;
        border-color: #d4d4d8;
    }
    .cv-skill-tag.neutral:hover {
        background: #3f3f46;
        color: #fff;
        border-color: #3f3f46;
    }

    /* ── Timeline / Experience ── */
    .cv-timeline { position: relative; padding-left: 1.5rem; }
    .cv-timeline::before {
        content: '';
        position: absolute;
        left: 0.4rem;
        top: 0.5rem;
        bottom: 0.5rem;
        width: 2px;
        background: linear-gradient(to bottom, #2d6e30, #e0c56a40);
        border-radius: 2px;
    }
    .cv-tl-item { position: relative; margin-bottom: 1.6rem; }
    .cv-tl-item:last-child { margin-bottom: 0; }
    .cv-tl-dot {
        position: absolute;
        left: -1.5rem;
        top: 0.35rem;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #2d6e30;
        border: 2px solid #fff;
        box-shadow: 0 0 0 2px #2d6e30;
    }
    .cv-tl-company {
        font-size: 1rem;
        font-weight: 700;
        color: #1a1a1a;
        margin-bottom: 0.15rem;
    }
    .cv-tl-role-row {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 0.65rem;
    }
    .cv-tl-role {
        font-size: 0.85rem;
        font-weight: 600;
        color: #fff;
        background: linear-gradient(135deg, #1e4d20, #2d6e30);
        padding: 0.18rem 0.65rem;
        border-radius: 50px;
    }
    .cv-tl-period {
        font-size: 0.78rem;
        color: #888;
        font-weight: 500;
    }
    .cv-tl-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .cv-tl-list li {
        position: relative;
        padding-left: 1.1rem;
        margin-bottom: 0.4rem;
        font-size: 0.9rem;
        color: #4a4a4a;
        line-height: 1.65;
    }
    .cv-tl-list li::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0.58rem;
        width: 5px;
        height: 5px;
        border-radius: 50%;
        background: #c9a82a;
    }

    /* ── Project card ── */
    .cv-project-card {
        border: 1px solid #e8f0e8;
        border-left: 4px solid #2d6e30;
        border-radius: 10px;
        padding: 1.1rem 1.25rem;
        background: #f8fcf8;
        transition: box-shadow 0.2s, border-color 0.2s;
    }
    .cv-project-card:hover {
        box-shadow: 0 4px 16px rgba(29,78,32,0.12);
        border-left-color: #e0c56a;
    }
    .cv-project-title {
        font-size: 1rem;
        font-weight: 700;
        color: #1a1a1a;
        margin-bottom: 0.2rem;
    }
    .cv-project-title a {
        color: #1e5c22;
        text-decoration: none;
        transition: color 0.2s;
    }
    .cv-project-title a:hover { color: #c9a82a; text-decoration: underline; }
    .cv-project-purpose {
        font-size: 0.85rem;
        color: #666;
        margin-bottom: 0.7rem;
        font-style: italic;
    }

    /* ── Scroll Animations ── */
    .cv-fade-up {
        opacity: 0;
        transform: translateY(22px);
        transition: opacity 0.55s ease, transform 0.55s ease;
    }
    .cv-fade-up.visible {
        opacity: 1;
        transform: translateY(0);
    }

    /* ── Responsive tweaks ── */
    @media (max-width: 768px) {
        .cv-hero { min-height: 48vh; }
        .cv-hero-content { padding: 1.5rem 1.25rem 2rem; }
        .cv-section { padding: 1.25rem 1.15rem; }
    }
</style>

<link rel="stylesheet" href="<?= base_url('assets/admin_lte/plugins/fontawesome-free/css/all.min.css') ?>">

<div class="cv-page">

    <!-- ════════ HERO ════════ -->
    <div class="cv-hero d-none d-lg-flex">
        <img class="cv-hero-bg" src="<?php echo base_url('assets/banner.jpg'); ?>" alt="Rom Paulo Sarmiento - CV Banner">
        <div class="cv-hero-overlay"></div>
        <div class="cv-hero-content">
            <div class="cv-hero-badge">Curriculum Vitae</div>
            <h1 class="cv-hero-name">Rom Paulo Sarmiento</h1>
            <p class="cv-hero-subtitle">Web Developer &amp; IT Professional &mdash; PHP · CodeIgniter · Docker · Linux</p>
            <div class="cv-hero-chips">
                <span class="cv-hero-chip"><i class="fa fa-map-marker"></i> Norzagaray, Bulacan, Philippines</span>
                <span class="cv-hero-chip"><i class="fa fa-briefcase"></i> Web Developer / Programmer</span>
                <a href="https://bms.rps-hom-lab.com" target="_blank" class="cv-hero-chip"><i class="fa fa-globe"></i> bms.rps-hom-lab.com</a>
            </div>
        </div>
    </div>

    <!-- Mobile heading (no banner) -->
    <div class="d-lg-none bg-white border-bottom px-3 py-4 mb-3">
        <div class="cv-hero-badge" style="background:#edf7ee;color:#1e5c22;border-color:#b5d9b8;">Curriculum Vitae</div>
        <h1 class="fw-bold fs-4 mb-1 mt-2">Rom Paulo Sarmiento</h1>
        <p class="text-muted" style="font-size:0.88rem;">Web Developer &amp; IT Professional</p>
    </div>

    <!-- ════════ CONTENT ════════ -->
    <div class="px-3 px-md-4 pb-5" style="max-width:900px;margin:0 auto;">

        <!-- Professional Summary -->
        <div class="cv-section cv-fade-up mt-4">
            <div class="cv-section-header">
                <div class="cv-section-icon"><i class="fa fa-user"></i></div>
                <h2 class="cv-section-title">Professional Summary</h2>
            </div>
            <p class="cv-summary-text">
                Highly motivated and results-driven professional with a strong foundation in both front-end and back-end web development,
                and a unique cross-functional background in IT support and system administration. Proven ability to optimize systems,
                automate workflows, and deliver tangible improvements in productivity and error reduction. Eager to leverage a
                comprehensive skill set to contribute to a dynamic team.
            </p>
        </div>

        <!-- Skills -->
        <div class="cv-section cv-fade-up">
            <div class="cv-section-header">
                <div class="cv-section-icon"><i class="fa fa-code"></i></div>
                <h2 class="cv-section-title">Skills</h2>
            </div>

            <div class="cv-skill-group">
                <div class="cv-skill-label">Languages &amp; Frameworks</div>
                <div class="cv-skill-tags">
                    <span class="cv-skill-tag primary">PHP</span>
                    <span class="cv-skill-tag primary">CodeIgniter</span>
                    <span class="cv-skill-tag primary">JavaScript</span>
                    <span class="cv-skill-tag primary">jQuery</span>
                    <span class="cv-skill-tag primary">HTML</span>
                    <span class="cv-skill-tag primary">CSS</span>
                    <span class="cv-skill-tag primary">WordPress</span>
                </div>
            </div>

            <div class="cv-skill-group">
                <div class="cv-skill-label">Databases</div>
                <div class="cv-skill-tags">
                    <span class="cv-skill-tag secondary">Oracle SQL</span>
                    <span class="cv-skill-tag secondary">MySQL</span>
                </div>
            </div>

            <div class="cv-skill-group">
                <div class="cv-skill-label">Tools &amp; Technologies</div>
                <div class="cv-skill-tags">
                    <span class="cv-skill-tag neutral">Docker</span>
                    <span class="cv-skill-tag neutral">Ubuntu Linux</span>
                    <span class="cv-skill-tag neutral">GitHub</span>
                    <span class="cv-skill-tag neutral">Bootstrap</span>
                    <span class="cv-skill-tag neutral">DataTable</span>
                    <span class="cv-skill-tag neutral">CI/CD Pipelines</span>
                </div>
            </div>
        </div>

        <!-- Professional Experience -->
        <div class="cv-section cv-fade-up">
            <div class="cv-section-header">
                <div class="cv-section-icon"><i class="fa fa-building"></i></div>
                <h2 class="cv-section-title">Professional Experience</h2>
            </div>

            <div class="cv-timeline">

                <!-- Job 1 -->
                <div class="cv-tl-item">
                    <div class="cv-tl-dot"></div>
                    <div class="cv-tl-company">Accent Micro Technologies &mdash; Pasig City, Philippines</div>
                    <div class="cv-tl-role-row">
                        <span class="cv-tl-role">Web Developer / Programmer</span>
                        <span class="cv-tl-period"><i class="fa fa-calendar me-1"></i>February 2024 – Present</span>
                    </div>
                    <ul class="cv-tl-list">
                        <li>Engineered and maintained internal systems using PHP and the CodeIgniter framework to simplify operational workflows and enhance efficiency.</li>
                        <li>Improved module performance and reduced user errors by implementing client-side validation and auto-populating search fields using JavaScript and jQuery.</li>
                        <li>Served as L3 Support, leveraging a deep understanding of internal systems to resolve critical technical challenges and drive direct system improvements.</li>
                        <li>Optimized critical SQL queries, reducing execution time by <strong>70–90%</strong> (e.g., from 30 minutes to 3 minutes), enhancing system efficiency and reducing hardware load.</li>
                    </ul>
                </div>

                <!-- Job 2 -->
                <div class="cv-tl-item">
                    <div class="cv-tl-dot"></div>
                    <div class="cv-tl-company">Accent Micro Technologies &mdash; Pasig City, Philippines</div>
                    <div class="cv-tl-role-row">
                        <span class="cv-tl-role">Cadet Engineer</span>
                        <span class="cv-tl-period"><i class="fa fa-calendar me-1"></i>March 2023 – February 2024</span>
                    </div>
                    <ul class="cv-tl-list">
                        <li>Gained a foundational understanding of hardware and network infrastructure by providing on-site support to clients, including server setup and maintenance.</li>
                        <li>Configured and maintained firewalls and switches to ensure network security and reliability.</li>
                        <li>Completed hands-on training with server virtualization, including the installation and configuration of virtual machines using VMware, Hyper-V, and VirtualBox.</li>
                    </ul>
                </div>

                <!-- Job 3 -->
                <div class="cv-tl-item">
                    <div class="cv-tl-dot"></div>
                    <div class="cv-tl-company">Number 1 Feeds Corporation</div>
                    <div class="cv-tl-role-row">
                        <span class="cv-tl-role">IT Technical Support</span>
                        <span class="cv-tl-period"><i class="fa fa-calendar me-1"></i>September 2022 – March 2023</span>
                    </div>
                    <ul class="cv-tl-list">
                        <li>Engineered spreadsheet automation using formulas and sheet linking, resulting in a <strong>15% increase</strong> in operations team productivity and a <strong>10% reduction</strong> in data entry errors.</li>
                        <li>Reduced loading errors by <strong>50%</strong> (from 1 error per 50 loads to 1 error per 100 loads) by establishing an organized system for tracking factory inventory.</li>
                        <li>Collaborated with the operations team to design a new loading policy approval system that streamlined the receipt-making process and prevented errors.</li>
                    </ul>
                </div>

            </div>
        </div>

        <!-- Projects -->
        <div class="cv-section cv-fade-up">
            <div class="cv-section-header">
                <div class="cv-section-icon"><i class="fa fa-laptop"></i></div>
                <h2 class="cv-section-title">Projects</h2>
            </div>

            <div class="cv-project-card">
                <div class="cv-project-title">
                    BMS — Business Management System &nbsp;
                    <a href="https://bms.rps-hom-lab.com" target="_blank"><i class="fa fa-external-link" style="font-size:0.8rem;"></i> bms.rps-hom-lab.com</a>
                </div>
                <div class="cv-project-purpose">Purpose: To help the company digitize their data and streamline business operations.</div>
                <ul class="cv-tl-list">
                    <li>Developed a system for creating and printing sales invoices and delivery receipts, enabling companies to digitize their receipt data while using pre-printed physical receipts.</li>
                    <li>Implemented functionality for tagging receipts and invoices as paid or unpaid, improving financial tracking.</li>
                    <li>Enabled powerful data analytics, providing insights into sales, product performance (highest sales), client order volume (highest order clients), and overall sales totals for various periods (monthly, yearly, custom ranges).</li>
                </ul>
            </div>

        </div>

    </div>
</div>

<script>
    // Scroll-triggered fade-up animations
    (function () {
        const els = document.querySelectorAll('.cv-fade-up');
        if (!els.length) return;
        const observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.08 });
        els.forEach(function (el, i) {
            el.style.transitionDelay = (i * 0.07) + 's';
            observer.observe(el);
        });
    })();
</script>

<?= $this->endSection() ?>