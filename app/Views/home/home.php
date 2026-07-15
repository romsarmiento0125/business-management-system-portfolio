`<?= $this->extend('layout') ?>

<?= $this->section('content') ?>

<link rel="stylesheet" href="<?= base_url('assets/my_css/home/home.css') ?>">
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

            <div class="cv-skill-group">
                <div class="cv-skill-label">AI &amp; Automation <span style="font-size:0.65rem;font-weight:500;color:#a0a0a0;text-transform:none;letter-spacing:0;">(Learning / In Progress)</span></div>
                <div class="cv-skill-tags">
                    <span class="cv-skill-tag ai-tag">n8n</span>
                    <span class="cv-skill-tag ai-tag">AI Agent Workflows</span>
                    <span class="cv-skill-tag ai-tag">Qdrant (RAG)</span>
                    <span class="cv-skill-tag ai-tag">Gemini API</span>
                    <span class="cv-skill-tag ai-tag">Conversational AI</span>
                </div>
            </div>
        </div>

        <style>
            .cv-skill-tag.ai-tag {
                background: #f3f0ff;
                color: #5b3fbf;
                border-color: #c4b5fd;
            }
            .cv-skill-tag.ai-tag:hover {
                background: #5b3fbf;
                color: #fff;
                border-color: #5b3fbf;
            }
        </style>

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

            <div class="cv-project-card mb-3">
                <!-- Title row -->
                <div style="display:flex;align-items:center;gap:0.6rem;flex-wrap:wrap;margin-bottom:0.2rem;">
                    <div class="cv-project-title" style="margin:0;">
                        BMS &mdash; Business Management System &nbsp;
                        <a href="https://bms.rps-hom-lab.com" target="_blank"><i class="fa fa-external-link" style="font-size:0.8rem;"></i> bms.rps-hom-lab.com</a>
                    </div>
                    <span style="font-size:0.7rem;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;background:#edf7ee;color:#1e5c22;border:1px solid #b5d9b8;padding:0.15rem 0.6rem;border-radius:50px;">
                        Live &amp; In Production
                    </span>
                </div>

                <div class="cv-project-purpose" style="margin-bottom:0.75rem;">
                    A custom-built, real-world Business Management System currently in active production use by a medium-sized company.
                    While still a young and growing system, it has proven to be genuinely useful in day-to-day business operations.
                </div>

                <!-- Key highlights -->
                <div style="margin-bottom:0.75rem;">
                    <div class="cv-skill-label" style="margin-bottom:0.4rem;">Key Features</div>
                    <ul class="cv-tl-list">
                        <li>Developed a system for creating and printing sales invoices and delivery receipts, enabling the company to digitize receipt data while continuing to use their pre-printed physical receipts.</li>
                        <li>Implemented paid / unpaid tagging on invoices and receipts for streamlined financial tracking.</li>
                        <li>Enabled powerful data analytics &mdash; insights into product performance (top-selling items), client order volume (highest-order clients), and sales totals across custom date ranges (monthly, yearly, and more).</li>
                    </ul>
                </div>

                <!-- Role & Ownership -->
                <div style="margin-bottom:0.75rem;">
                    <div class="cv-skill-label" style="margin-bottom:0.4rem;">Role &amp; Ownership</div>
                    <ul class="cv-tl-list">
                        <li><strong>Sole developer, architect, and DevOps engineer</strong> &mdash; solely responsible for the full system lifecycle: design, development, deployment, server setup, and ongoing maintenance.</li>
                        <li>Collaborated closely with the <strong>company president</strong> to gather requirements and ensure the system is tailored precisely to the company&rsquo;s operational needs.</li>
                        <li>Self-hosted on a personal server using Docker and Ubuntu Linux, with GitHub CI/CD pipelines for automated deployments.</li>
                    </ul>
                </div>

                <!-- Portfolio note -->
                <!-- IP & Portfolio note -->
                <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:0.65rem 0.9rem;display:flex;align-items:flex-start;gap:0.6rem;margin-bottom:0.5rem;">
                    <i class="fa fa-exclamation-circle" style="color:#d97706;margin-top:0.15rem;flex-shrink:0;"></i>
                    <p style="margin:0;font-size:0.82rem;color:#92400e;line-height:1.6;">
                        <strong>IP &amp; Portfolio Disclosure:</strong> The intellectual property of this system belongs to the client company. It is showcased here <strong>with their permission</strong> as a portfolio demonstration of my ability to design, build, deploy, and maintain a real end-to-end production system independently.
                    </p>
                </div>

                <!-- For sale note -->
                <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:0.65rem 0.9rem;display:flex;align-items:flex-start;gap:0.6rem;">
                    <i class="fa fa-tag" style="color:#2563eb;margin-top:0.15rem;flex-shrink:0;"></i>
                    <p style="margin:0;font-size:0.82rem;color:#1e3a8a;line-height:1.6;">
                        <strong>Available for Sale:</strong> This system is open for acquisition. Interested businesses can get a fully customized version tailored to their specific workflow and operational needs &mdash; new features and modules can be added based on requirements.
                    </p>
                </div>
            </div>

            <!-- DANA AI Project -->
            <div class="cv-project-card" style="border-left-color:#7c3aed;border-color:#ede9fe;background:#faf8ff;">
                <div style="display:flex;align-items:center;gap:0.6rem;flex-wrap:wrap;margin-bottom:0.2rem;">
                    <div class="cv-project-title" style="margin:0;">DANA AI — Data Analyst AI</div>
                    <span style="font-size:0.7rem;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;background:#ede9fe;color:#5b3fbf;border:1px solid #c4b5fd;padding:0.15rem 0.6rem;border-radius:50px;">In Development</span>
                </div>
                <div class="cv-project-purpose" style="margin-bottom:0.5rem;">Purpose: An AI-powered data analyst integrated with BMS, allowing users to query business data through natural language conversation.</div>

                <div style="margin-bottom:0.65rem;">
                    <div class="cv-skill-label" style="margin-bottom:0.4rem;">Tech Stack</div>
                    <div class="cv-skill-tags">
                        <span class="cv-skill-tag ai-tag">n8n</span>
                        <span class="cv-skill-tag ai-tag">AI Agent</span>
                        <span class="cv-skill-tag ai-tag">Qdrant (RAG)</span>
                        <span class="cv-skill-tag ai-tag">Gemini API</span>
                        <span class="cv-skill-tag secondary">MySQL</span>
                        <span class="cv-skill-tag neutral">Simple Memory</span>
                    </div>
                </div>

                <div style="margin-bottom:0.65rem;">
                    <div class="cv-skill-label" style="margin-bottom:0.4rem;">Current Capabilities</div>
                    <ul class="cv-tl-list">
                        <li>Users can ask questions about Sales Invoices (SI) and Delivery Receipts (DR) — such as volumes, sales totals, and unpaid status — through a natural language chat interface.</li>
                        <li>Built on an n8n AI Agent workflow connected to the live BMS MySQL database, powered by Gemini API for language understanding and Qdrant for retrieval-augmented generation (RAG).</li>
                        <li>Supports conversational context through simple memory, enabling follow-up questions within a session.</li>
                    </ul>
                </div>

                <div style="border-top:1px solid #ede9fe;padding-top:0.65rem;">
                    <div class="cv-skill-label" style="margin-bottom:0.4rem;color:#7c3aed;"><i class="fa fa-lightbulb-o me-1"></i> Future Vision</div>
                    <ul class="cv-tl-list">
                        <li>Automated report generation — DANA will produce formatted business reports on demand.</li>
                        <li>Predictive product insights — identifying which products are most profitable and worth focusing on.</li>
                        <li>Client risk analysis — flagging clients with a higher likelihood of growth or payment risk.</li>
                        <li>Strategic business recommendations to help guide company decisions for the future.</li>
                    </ul>
                </div>
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

<?= $this->endSection() ?>`