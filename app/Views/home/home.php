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
            <p class="cv-hero-subtitle">Web Developer &amp; IT Professional</p>
            <div class="cv-hero-chips">
                <span class="cv-hero-chip"><i class="fa fa-map-marker"></i> Norzagaray, Bulacan, 3013, Philippines</span>
                <span class="cv-hero-chip"><i class="fa fa-envelope"></i> rompaulosarmiento0125@gmail.com</span>
                <span class="cv-hero-chip"><i class="fa fa-phone"></i> 09754254702</span>
                <a href="https://bms.rps-home-lab.com" target="_blank" class="cv-hero-chip"><i class="fa fa-globe"></i> bms.rps-home-lab.com</a>
                <span class="cv-hero-chip" style="background: rgba(224, 197, 106, 0.2); border-color: rgba(224, 197, 106, 0.45); color: #fff;"><i class="fa fa-key"></i> admin / @Admin123</span>
            </div>
        </div>
    </div>

    <!-- Mobile heading (no banner) -->
    <div class="d-lg-none bg-white border-bottom px-3 py-4 mb-3">
        <div class="cv-hero-badge" style="background:#edf7ee;color:#1e5c22;border-color:#b5d9b8;">Curriculum Vitae</div>
        <h1 class="fw-bold fs-4 mb-1 mt-2">Rom Paulo Sarmiento</h1>
        <p class="text-muted mb-3" style="font-size:0.88rem;">Web Developer &amp; IT Professional</p>
        <div class="d-flex flex-column gap-2" style="font-size:0.8rem; color:#555;">
            <div><i class="fa fa-map-marker text-success me-2" style="width:14px;"></i> Norzagaray, Bulacan, 3013, Philippines</div>
            <div><i class="fa fa-envelope text-success me-2" style="width:14px;"></i> rompaulosarmiento0125@gmail.com</div>
            <div><i class="fa fa-phone text-success me-2" style="width:14px;"></i> 09754254702</div>
            <div><i class="fa fa-globe text-success me-2" style="width:14px;"></i> <a href="https://bms.rps-home-lab.com" target="_blank" class="text-success text-decoration-none">bms.rps-home-lab.com</a> <span class="badge bg-warning text-dark ms-1">admin / @Admin123</span></div>
        </div>
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
                Results-driven Web Developer and IT Professional with extensive experience in full-stack web application design, database performance optimization, and systems administration. Proven track record in architecting custom production-grade systems, optimizing database execution times by up to 90%, and streamlining operational workflows. Hands-on experience in cloud infrastructure deployment, Linux systems, CI/CD automation, and emerging AI/RAG agent workflows.
            </p>
        </div>

        <!-- Education -->
        <div class="cv-section cv-fade-up">
            <div class="cv-section-header">
                <div class="cv-section-icon"><i class="fa fa-graduation-cap"></i></div>
                <h2 class="cv-section-title">Education</h2>
            </div>
            <div class="ms-1">
                <div class="fw-bold text-dark" style="font-size:0.95rem;">Polytechnic University of the Philippines &mdash; Sta. Maria, Bulacan Campus</div>
                <div class="d-flex align-items-center flex-wrap gap-2 mt-1">
                    <span class="badge px-2.5 py-1.5" style="background: linear-gradient(135deg, #1e4d20, #2d6e30); color:#e0c56a; border-radius:50px; font-weight:600; font-size:0.8rem;">Bachelor of Science in Information Technology (BSIT)</span>
                    <span class="text-muted" style="font-size:0.8rem; font-weight:500;"><i class="fa fa-calendar me-1"></i>Graduated: Nov 2022</span>
                </div>
            </div>
        </div>

        <!-- Skills -->
        <div class="cv-section cv-fade-up">
            <div class="cv-section-header">
                <div class="cv-section-icon"><i class="fa fa-code"></i></div>
                <h2 class="cv-section-title">Core Competencies &amp; Technical Skills</h2>
            </div>

            <div class="cv-skill-group">
                <div class="cv-skill-label">Programming &amp; Frameworks</div>
                <div class="cv-skill-tags">
                    <span class="cv-skill-tag primary">PHP</span>
                    <span class="cv-skill-tag primary">CodeIgniter 4</span>
                    <span class="cv-skill-tag primary">PHPUnit (Unit Testing)</span>
                    <span class="cv-skill-tag primary">JavaScript</span>
                    <span class="cv-skill-tag primary">jQuery</span>
                    <span class="cv-skill-tag primary">Bootstrap</span>
                </div>
            </div>

            <div class="cv-skill-group">
                <div class="cv-skill-label">Databases &amp; Optimization</div>
                <div class="cv-skill-tags">
                    <span class="cv-skill-tag secondary">MySQL</span>
                    <span class="cv-skill-tag secondary">Oracle SQL</span>
                    <span class="cv-skill-tag secondary">Query Optimization</span>
                </div>
            </div>

            <div class="cv-skill-group">
                <div class="cv-skill-label">DevOps &amp; Infrastructure</div>
                <div class="cv-skill-tags">
                    <span class="cv-skill-tag neutral">Docker</span>
                    <span class="cv-skill-tag neutral">Ubuntu Linux</span>
                    <span class="cv-skill-tag neutral">GitHub Actions (CI/CD)</span>
                    <span class="cv-skill-tag neutral">Nginx</span>
                    <span class="cv-skill-tag neutral">L3 Systems Support</span>
                </div>
            </div>

            <div class="cv-skill-group">
                <div class="cv-skill-label">AI &amp; Workflows (Emerging)</div>
                <div class="cv-skill-tags">
                    <span class="cv-skill-tag ai-tag">n8n Workflow Automation</span>
                    <span class="cv-skill-tag ai-tag">AI Agents</span>
                    <span class="cv-skill-tag ai-tag">Qdrant (RAG)</span>
                    <span class="cv-skill-tag ai-tag">Gemini API</span>
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
                <h2 class="cv-section-title">Work Experience</h2>
            </div>

            <div class="cv-timeline">

                <!-- Job 1 -->
                <div class="cv-tl-item">
                    <div class="cv-tl-dot"></div>
                    <div class="cv-tl-company">Accent Micro Technologies Inc. (AMTI)</div>
                    <div class="text-muted mb-2" style="font-size:0.78rem; line-height:1.4;">
                        <i class="fa fa-map-marker me-1"></i>7F ALVA Business Center, 259 C. Raymundo Avenue, Brgy. Maybunga, Pasig City, 1607 Philippines &nbsp;|&nbsp; <i class="fa fa-phone me-1"></i>(+632) 8988.9788 / 5322.2800
                    </div>
                    <div class="cv-tl-role-row">
                        <span class="cv-tl-role">Web Developer / Programmer</span>
                        <span class="cv-tl-period"><i class="fa fa-calendar me-1"></i>Feb 2024 – Present</span>
                    </div>
                    <ul class="cv-tl-list">
                        <li><strong>Full-Lifecycle Module Development:</strong> Engineered 5 custom internal modules from scratch utilizing PHP and CodeIgniter while refactoring and reviving inactive legacy modules to expand platform features for data presentation and executive report generation.</li>
                        <li><strong>System Maintenance &amp; Scaling:</strong> Co-maintain and optimize 10+ core system modules, ensuring uninterrupted data processing and reporting workflows across internal departments.</li>
                        <li><strong>Query Optimization:</strong> Optimized high-overhead SQL queries, reducing database execution times by <strong>70–90%</strong> (e.g., accelerating batch query processes from 30 minutes down to 3 minutes), significantly lowering server memory overhead.</li>
                        <li><strong>UX &amp; Support:</strong> Enhanced front-end user experience with dynamic JavaScript/jQuery validation and auto-population, while serving as L3 Support engineer to deliver long-term code-level fixes.</li>
                    </ul>
                </div>

                <!-- Job 2 -->
                <div class="cv-tl-item">
                    <div class="cv-tl-dot"></div>
                    <div class="cv-tl-company">Accent Micro Technologies Inc. (AMTI)</div>
                    <div class="text-muted mb-2" style="font-size:0.78rem; line-height:1.4;">
                        <i class="fa fa-map-marker me-1"></i>7F ALVA Business Center, 259 C. Raymundo Avenue, Brgy. Maybunga, Pasig City, 1607 Philippines &nbsp;|&nbsp; <i class="fa fa-phone me-1"></i>(+632) 8988.9788 / 5322.2800
                    </div>
                    <div class="cv-tl-role-row">
                        <span class="cv-tl-role">Cadet Engineer</span>
                        <span class="cv-tl-period"><i class="fa fa-calendar me-1"></i>Mar 2023 – Feb 2024</span>
                    </div>
                    <ul class="cv-tl-list">
                        <li><strong>Systems &amp; Virtualization Infrastructure:</strong> Provisioned, configured, and maintained physical server hardware, firewalls, switches, and virtualized server environments across VMware, Hyper-V, and VirtualBox.</li>
                        <li><strong>Technical Adaptability:</strong> Leveraged strong foundational full-stack web development skills to rapidly transition into a full-time Web Developer role upon internal opening.</li>
                    </ul>
                </div>

                <!-- Job 3 -->
                <div class="cv-tl-item">
                    <div class="cv-tl-dot"></div>
                    <div class="cv-tl-company">Number 1 Feeds Corporation</div>
                    <div class="text-muted mb-2" style="font-size:0.78rem; line-height:1.4;">
                        <i class="fa fa-map-marker me-1"></i>Villarama, Norzagaray, Bulacan, Philippines
                    </div>
                    <div class="cv-tl-role-row">
                        <span class="cv-tl-role">IT Technical Support</span>
                        <span class="cv-tl-period"><i class="fa fa-calendar me-1"></i>Sep 2022 – Mar 2023</span>
                    </div>
                    <ul class="cv-tl-list">
                        <li>Streamlined factory inventory mechanisms and built digital approval workflows, reducing loading errors by <strong>50%</strong> and improving team productivity by <strong>15%</strong> through spreadsheet automation.</li>
                    </ul>
                </div>

            </div>
        </div>

        <!-- Projects -->
        <div class="cv-section cv-fade-up">
            <div class="cv-section-header">
                <div class="cv-section-icon"><i class="fa fa-laptop"></i></div>
                <h2 class="cv-section-title">Key Projects</h2>
            </div>

            <!-- BMS Project -->
            <div class="cv-project-card mb-4">
                <!-- Title row -->
                <div class="d-flex align-items-center gap-2 flex-wrap mb-2">
                    <div class="cv-project-title m-0">
                        Business Management System (BMS) — Sole Architect &amp; Full-Stack Developer
                    </div>
                </div>
                
                <div class="mb-3">
                    <span class="badge bg-success text-white px-2 py-1" style="background:#edf7ee;color:#1e5c22;border:1px solid #b5d9b8; font-size:0.75rem; font-weight:600; border-radius:50px;">
                        Live &amp; In Production
                    </span>
                    <a href="https://bms.rps-home-lab.com" target="_blank" class="ms-2 text-decoration-none text-success fw-600" style="font-size:0.85rem;"><i class="fa fa-external-link"></i> bms.rps-home-lab.com</a>
                    <span class="badge bg-warning text-dark ms-2" style="font-size:0.75rem; font-weight:600; border-radius:50px;"><i class="fa fa-key me-1"></i> admin / @Admin123</span>
                </div>

                <!-- Highlights list -->
                <ul class="cv-tl-list mb-3">
                    <li><strong>Client-Adopted Production System:</strong> Independently pitched, developed, and deployed a custom business management solution for Number 1 Feeds Corporation to solve real-world operational bottlenecks following prior experience at the company.</li>
                    <li><strong>Full Lifecycle &amp; Ownership:</strong> Handled 100% of the project lifecycle—from client consultation and requirements gathering with executive leadership to system architecture, UI/UX, database design, and ongoing deployment.</li>
                    <li><strong>Financial &amp; Order Operations:</strong> Engineered invoice and delivery receipt workflows that digitized order tracking while maintaining compatibility with pre-printed physical receipts; implemented real-time invoice payment tagging (Paid/Unpaid) for financial reporting.</li>
                    <li><strong>Business Analytics &amp; Reporting:</strong> Built custom analytics tools to track product velocity, identify top-volume clients, and generate multi-period financial summaries.</li>
                    <li><strong>DevOps &amp; Infrastructure:</strong> Configured and self-hosted the application environment on Ubuntu Linux using Docker containers and automated deployments via GitHub CI/CD pipelines.</li>
                    <li><strong>Code Reliability &amp; Testing:</strong> Integrated PHPUnit test cases across report generation and data pipeline modules, reducing system-wide bugs and regressions by <strong>30%</strong>.</li>
                </ul>

                <!-- Note on Portfolio Demo -->
                <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:0.65rem 0.9rem;display:flex;align-items:flex-start;gap:0.6rem;margin-bottom:0.5rem;">
                    <i class="fa fa-exclamation-circle" style="color:#d97706;margin-top:0.15rem;flex-shrink:0;"></i>
                    <p style="margin:0;font-size:0.82rem;color:#92400e;line-height:1.6;">
                        <strong>Note on Portfolio Demo:</strong> The live public link above is an isolated demo clone using sample/mock data. The production system deployed with Number 1 Feeds Corporation operates on a dedicated private environment to protect client proprietary data.
                    </p>
                </div>
            </div>

            <!-- DANA AI Project -->
            <div class="cv-project-card" style="border-left-color:#7c3aed;border-color:#ede9fe;background:#faf8ff;">
                <div class="d-flex align-items-center gap-2 flex-wrap mb-2">
                    <div class="cv-project-title m-0">
                        DANA AI (Data Analyst AI Agent) — BMS Executive Intelligence Module
                    </div>
                </div>

                <div class="mb-3">
                    <span class="badge px-2 py-1" style="background:#ede9fe;color:#5b3fbf;border:1px solid #c4b5fd; font-size:0.75rem; font-weight:600; border-radius:50px;">
                        User Testing &amp; Active Validation
                    </span>
                </div>

                <ul class="cv-tl-list">
                    <li><strong>Executive Problem-Solving:</strong> Designed as an intelligent extension of the BMS database to fulfill real-time, ad-hoc data requests from company executives and higher management without manual developer intervention.</li>
                    <li><strong>Instant Business Intelligence:</strong> Enables non-technical decision-makers to ask complex, natural-language questions about Sales Invoices, Delivery Receipts, and financial metrics, returning immediate data insights.</li>
                    <li><strong>Active Deployment Stage:</strong> Currently in user testing phase; functional and actively utilized for ad-hoc querying while undergoing ongoing response validation and accuracy calibration against production database benchmarks.</li>
                    <li><strong>Architecture &amp; Tech Stack:</strong> Integrated directly with the BMS MySQL database using an n8n workflow pipeline, Qdrant vector database (RAG), and Gemini API for context-aware language understanding and retrieval.</li>
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

<?= $this->endSection() ?>`