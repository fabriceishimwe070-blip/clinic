<?php session_start(); if(!empty($_SESSION["patient_id"])){header("Location: home.php");exit;} ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>ProClinic — Healthcare, Reimagined</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Syne:wght@300;400;500;600&display=swap" rel="stylesheet"/>
<style>
:root {
  --navy: #060f1e;
  --navy2: #0b1d35;
  --navy3: #0f2748;
  --teal: #1fbd8c;
  --teal2: #17a87c;
  --teal-dim: rgba(31,189,140,.15);
  --gold: #d4a843;
  --gold-dim: rgba(212,168,67,.12);
  --white: #f0f5ee;
  --white-dim: rgba(240,245,238,.55);
  --white-ghost: rgba(240,245,238,.07);
  --border: rgba(240,245,238,.1);
  --border2: rgba(31,189,140,.25);
}
*, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
html { scroll-behavior:smooth; }

body {
  background:var(--navy);
  font-family:'Syne',sans-serif;
  color:var(--white);
  overflow-x:hidden;
}

/* ── GRID TEXTURE ── */
body::before {
  content:'';
  position:fixed;
  inset:0;
  background-image:
    linear-gradient(rgba(31,189,140,.03) 1px, transparent 1px),
    linear-gradient(90deg, rgba(31,189,140,.03) 1px, transparent 1px);
  background-size:56px 56px;
  pointer-events:none;
  z-index:0;
}

/* ── NAV ── */
nav {
  position:fixed; top:0; left:0; right:0; z-index:100;
  padding:1.4rem 5vw;
  display:flex; align-items:center; justify-content:space-between;
  background:rgba(6,15,30,.7);
  backdrop-filter:blur(16px);
  border-bottom:1px solid var(--border);
}
.nav-logo {
  display:flex; align-items:center; gap:10px;
}
.nav-logo-mark {
  width:36px; height:36px; border-radius:10px;
  background:linear-gradient(135deg,var(--teal),var(--teal2));
  display:flex; align-items:center; justify-content:center;
  font-size:.9rem;
}
.nav-logo-text {
  font-family:'Playfair Display',serif;
  font-size:1.35rem; font-weight:700;
  letter-spacing:.3px; color:#fff;
}
.nav-logo-text span { color:var(--teal); }
.nav-links {
  display:flex; align-items:center; gap:2.5rem;
  list-style:none;
}
.nav-links a {
  color:var(--white-dim);
  text-decoration:none;
  font-size:.85rem; font-weight:500;
  letter-spacing:.5px;
  text-transform:uppercase;
  transition:.2s;
}
.nav-links a:hover { color:var(--teal); }
.nav-cta {
  padding:.65rem 1.5rem;
  background:var(--teal);
  color:#fff !important;
  border-radius:50px;
  font-size:.85rem !important;
  font-weight:600 !important;
  letter-spacing:.3px !important;
  text-transform:uppercase !important;
  transition:all .25s !important;
}
.nav-cta:hover {
  background:var(--teal2) !important;
  transform:translateY(-1px);
  box-shadow:0 8px 20px rgba(31,189,140,.3) !important;
  color:#fff !important;
}

/* ── HERO ── */
.hero {
  position:relative; z-index:1;
  min-height:100vh;
  display:flex; align-items:center;
  padding:8rem 5vw 5rem;
  overflow:hidden;
}
.hero-orb {
  position:absolute;
  border-radius:50%;
  pointer-events:none;
}
.hero-orb-1 {
  width:600px; height:600px;
  background:radial-gradient(circle,rgba(31,189,140,.12),transparent 70%);
  top:-150px; right:-100px;
  animation:pulse 8s ease-in-out infinite;
}
.hero-orb-2 {
  width:400px; height:400px;
  background:radial-gradient(circle,rgba(212,168,67,.07),transparent 70%);
  bottom:-100px; left:10%;
  animation:pulse 12s ease-in-out infinite reverse;
}
@keyframes pulse {
  0%,100%{transform:scale(1) translate(0,0);}
  50%{transform:scale(1.08) translate(15px,-10px);}
}
.hero-inner {
  max-width:700px;
  position:relative; z-index:2;
}
.hero-badge {
  display:inline-flex; align-items:center; gap:8px;
  padding:.45rem 1.1rem;
  background:var(--teal-dim);
  border:1px solid var(--border2);
  border-radius:50px;
  font-size:.8rem; font-weight:600;
  letter-spacing:.8px; text-transform:uppercase;
  color:var(--teal);
  margin-bottom:2rem;
  animation:fadeUp .8s ease both;
}
.hero-badge-dot {
  width:7px; height:7px; border-radius:50%;
  background:var(--teal);
  animation:blink 2s ease infinite;
}
@keyframes blink {
  0%,100%{opacity:1;}50%{opacity:.3;}
}
.hero-headline {
  font-family:'Playfair Display',serif;
  font-size:clamp(3rem,6vw,5.5rem);
  font-weight:700; line-height:1.08;
  letter-spacing:-.02em;
  margin-bottom:1.5rem;
  animation:fadeUp .8s .15s ease both;
}
.hero-headline em {
  font-style:italic; color:var(--teal);
}
.hero-sub {
  font-size:1.1rem;
  color:var(--white-dim);
  line-height:1.75;
  max-width:520px;
  margin-bottom:2.5rem;
  font-weight:300;
  animation:fadeUp .8s .3s ease both;
}
.hero-actions {
  display:flex; align-items:center; gap:1.2rem; flex-wrap:wrap;
  animation:fadeUp .8s .45s ease both;
}
.btn-primary {
  padding:.9rem 2.2rem;
  background:var(--teal);
  color:#fff;
  border:none; border-radius:50px;
  font-family:'Syne',sans-serif;
  font-size:1rem; font-weight:600;
  cursor:pointer; text-decoration:none;
  display:inline-flex; align-items:center; gap:.5rem;
  transition:all .25s;
}
.btn-primary:hover {
  background:var(--teal2);
  transform:translateY(-2px);
  box-shadow:0 12px 30px rgba(31,189,140,.35);
}
.btn-ghost {
  padding:.9rem 2.2rem;
  background:transparent;
  color:var(--white);
  border:1px solid var(--border);
  border-radius:50px;
  font-family:'Syne',sans-serif;
  font-size:1rem; font-weight:500;
  cursor:pointer; text-decoration:none;
  display:inline-flex; align-items:center; gap:.5rem;
  transition:all .25s;
}
.btn-ghost:hover {
  border-color:var(--teal);
  color:var(--teal);
}
.hero-stats {
  display:flex; gap:3rem;
  margin-top:4rem;
  padding-top:2.5rem;
  border-top:1px solid var(--border);
  animation:fadeUp .8s .6s ease both;
}
.hero-stat-num {
  font-family:'Playfair Display',serif;
  font-size:2.4rem; font-weight:700;
  color:var(--teal); line-height:1;
  margin-bottom:.3rem;
}
.hero-stat-lbl {
  font-size:.8rem; font-weight:500;
  color:var(--white-dim);
  letter-spacing:.5px; text-transform:uppercase;
}
.hero-visual {
  position:absolute;
  right:5vw; top:50%;
  transform:translateY(-50%);
  width:38vw; max-width:520px;
  animation:fadeRight .9s .3s ease both;
  z-index:2;
}
@keyframes fadeRight {
  from{opacity:0;transform:translateY(-50%) translateX(40px);}
  to{opacity:1;transform:translateY(-50%) translateX(0);}
}
.hero-card-stack {
  position:relative; width:100%; aspect-ratio:1;
}
.hero-card {
  position:absolute;
  background:var(--navy2);
  border:1px solid var(--border);
  border-radius:20px;
  padding:1.5rem;
  backdrop-filter:blur(8px);
}
.hc-main {
  width:88%; left:6%; top:15%;
  box-shadow:0 30px 60px rgba(0,0,0,.4);
  z-index:3;
}
.hc-behind {
  width:75%; left:12.5%; top:5%;
  opacity:.6; z-index:2;
  transform:rotate(-3deg);
}
.hc-front {
  width:62%; right:-8%; bottom:15%;
  z-index:4;
  background:linear-gradient(135deg,var(--navy3),rgba(31,189,140,.1));
  border-color:var(--border2);
}
.card-header { display:flex; align-items:center; gap:10px; margin-bottom:1rem; }
.card-avatar {
  width:36px; height:36px; border-radius:50%;
  background:linear-gradient(135deg,var(--teal),var(--teal2));
  display:flex; align-items:center; justify-content:center;
  font-size:.8rem; font-weight:700; color:#fff;
}
.card-name { font-size:.9rem; font-weight:600; }
.card-role { font-size:.75rem; color:var(--white-dim); }
.card-dividers { display:flex; flex-direction:column; gap:.5rem; }
.card-divider {
  height:6px; border-radius:3px;
  background:var(--white-ghost);
}
.card-divider:nth-child(1){ width:85%; }
.card-divider:nth-child(2){ width:65%; }
.card-divider:nth-child(3){ width:75%; }
.card-status {
  display:inline-flex; align-items:center; gap:6px;
  padding:.3rem .8rem;
  background:rgba(31,189,140,.15);
  border:1px solid var(--border2);
  border-radius:50px;
  font-size:.75rem; font-weight:600;
  color:var(--teal);
  margin-top:1rem;
}
.card-status-dot { width:6px; height:6px; border-radius:50%; background:var(--teal); }
.mini-chart {
  display:flex; align-items:flex-end; gap:4px;
  height:40px; margin-bottom:.5rem;
}
.mini-bar {
  flex:1; border-radius:3px 3px 0 0;
  background:var(--teal-dim);
  border:1px solid var(--border2);
  transition:.3s;
}
.mini-bar.active { background:var(--teal); }
.mini-label { font-size:.7rem; color:var(--teal); font-weight:600; }
.mini-sublabel { font-size:.65rem; color:var(--white-dim); }

@keyframes fadeUp {
  from{opacity:0;transform:translateY(24px);}
  to{opacity:1;transform:translateY(0);}
}

/* ── MARQUEE ── */
.marquee-strip {
  position:relative; z-index:1;
  padding:1.4rem 0;
  border-top:1px solid var(--border);
  border-bottom:1px solid var(--border);
  background:rgba(15,39,72,.4);
  overflow:hidden;
  white-space:nowrap;
}
.marquee-track {
  display:inline-block;
  animation:marquee 28s linear infinite;
}
.marquee-item {
  display:inline-flex; align-items:center; gap:.7rem;
  margin:0 2.5rem;
  font-size:.8rem; font-weight:500;
  letter-spacing:.6px; text-transform:uppercase;
  color:var(--white-dim);
}
.marquee-sep {
  width:4px; height:4px; border-radius:50%;
  background:var(--teal); opacity:.5;
}
@keyframes marquee {
  from{transform:translateX(0);}
  to{transform:translateX(-50%);}
}

/* ── SECTIONS ── */
section {
  position:relative; z-index:1;
  padding:7rem 5vw;
}
.section-label {
  display:inline-flex; align-items:center; gap:8px;
  font-size:.75rem; font-weight:600;
  letter-spacing:1.2px; text-transform:uppercase;
  color:var(--teal);
  margin-bottom:1.2rem;
}
.section-label::before {
  content:'';
  display:block; width:24px; height:2px;
  background:var(--teal); border-radius:1px;
}
.section-heading {
  font-family:'Playfair Display',serif;
  font-size:clamp(2rem,4vw,3.4rem);
  font-weight:700; line-height:1.15;
  letter-spacing:-.02em;
  margin-bottom:1rem;
}
.section-heading em { font-style:italic; color:var(--teal); }
.section-sub {
  font-size:1rem; color:var(--white-dim);
  line-height:1.75; max-width:540px;
  font-weight:300;
}

/* ── FEATURES ── */
.features-grid {
  display:grid;
  grid-template-columns:repeat(auto-fit,minmax(280px,1fr));
  gap:1.5px;
  margin-top:5rem;
  border:1.5px solid var(--border);
  border-radius:24px;
  overflow:hidden;
}
.feat-cell {
  background:var(--navy2);
  padding:2.5rem;
  transition:.3s;
  cursor:default;
}
.feat-cell:hover {
  background:var(--navy3);
}
.feat-icon {
  width:52px; height:52px; border-radius:14px;
  display:flex; align-items:center; justify-content:center;
  font-size:1.5rem;
  margin-bottom:1.5rem;
}
.feat-icon.green { background:var(--teal-dim); }
.feat-icon.gold { background:var(--gold-dim); }
.feat-title {
  font-size:1.1rem; font-weight:600;
  margin-bottom:.7rem; line-height:1.3;
}
.feat-desc {
  font-size:.9rem; color:var(--white-dim);
  line-height:1.7; font-weight:300;
}
.feat-link {
  display:inline-flex; align-items:center; gap:5px;
  margin-top:1.2rem;
  font-size:.82rem; font-weight:600;
  color:var(--teal); text-decoration:none;
  letter-spacing:.3px;
  transition:.2s;
}
.feat-link:hover { gap:8px; }

/* ── DIVISIONS ── */
.divisions-section { background:var(--navy2); }
.divisions-section::before {
  content:'';
  position:absolute; inset:0;
  background:radial-gradient(ellipse 60% 50% at 80% 50%,rgba(31,189,140,.06),transparent);
  pointer-events:none;
}
.divisions-inner {
  display:grid; grid-template-columns:1fr 1fr;
  gap:5rem; align-items:center;
}
.divisions-list {
  display:grid; grid-template-columns:1fr 1fr;
  gap:1rem; margin-top:2rem;
}
.div-pill {
  display:flex; align-items:center; gap:.8rem;
  padding:1rem 1.2rem;
  background:var(--white-ghost);
  border:1px solid var(--border);
  border-radius:14px;
  font-size:.9rem; font-weight:500;
  cursor:default; transition:.25s;
}
.div-pill:hover {
  background:var(--teal-dim);
  border-color:var(--border2);
  transform:translateX(4px);
}
.div-emoji { font-size:1.3rem; }

.divisions-visual {
  position:relative;
}
.appt-mockup {
  background:var(--navy);
  border:1px solid var(--border);
  border-radius:24px;
  padding:2rem;
  box-shadow:0 40px 80px rgba(0,0,0,.5);
}
.appt-row {
  display:flex; align-items:center; gap:1rem;
  padding:1rem;
  border-radius:12px;
  margin-bottom:.7rem;
  transition:.2s;
}
.appt-row:hover { background:var(--white-ghost); }
.appt-row-dot {
  width:10px; height:10px; border-radius:50%; flex-shrink:0;
}
.appt-info { flex:1; }
.appt-doctor { font-size:.9rem; font-weight:600; }
.appt-div { font-size:.78rem; color:var(--white-dim); }
.appt-badge {
  padding:.25rem .75rem;
  border-radius:50px;
  font-size:.72rem; font-weight:600;
}
.badge-accepted { background:rgba(31,189,140,.15); color:var(--teal); border:1px solid var(--border2); }
.badge-pending { background:rgba(212,168,67,.12); color:var(--gold); border:1px solid rgba(212,168,67,.25); }
.badge-done { background:rgba(100,200,100,.1); color:#8de08d; border:1px solid rgba(100,200,100,.2); }
.appt-mockup-header {
  display:flex; align-items:center; justify-content:space-between;
  margin-bottom:1.5rem;
  padding-bottom:1rem;
  border-bottom:1px solid var(--border);
}
.appt-mockup-title { font-size:1rem; font-weight:600; }
.appt-mockup-btn {
  padding:.4rem 1rem;
  background:var(--teal);
  border:none; border-radius:50px;
  color:#fff; font-size:.78rem; font-weight:600;
  cursor:pointer;
}

/* ── HOW IT WORKS ── */
.steps {
  display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
  gap:2rem; margin-top:4rem;
  position:relative;
}
.steps::before {
  content:'';
  position:absolute;
  top:2.4rem; left:10%; right:10%;
  height:1px;
  background:linear-gradient(90deg,transparent,var(--border),var(--border),transparent);
  pointer-events:none;
}
.step {
  display:flex; flex-direction:column; align-items:center;
  text-align:center;
}
.step-num {
  width:48px; height:48px; border-radius:50%;
  background:var(--navy2);
  border:1px solid var(--border2);
  display:flex; align-items:center; justify-content:center;
  font-family:'Playfair Display',serif;
  font-size:1.2rem; font-weight:700; color:var(--teal);
  margin-bottom:1.5rem;
  position:relative; z-index:1;
  transition:.3s;
}
.step:hover .step-num {
  background:var(--teal-dim);
  border-color:var(--teal);
  transform:scale(1.1);
}
.step-title { font-size:1rem; font-weight:600; margin-bottom:.5rem; }
.step-desc { font-size:.87rem; color:var(--white-dim); line-height:1.65; font-weight:300; }

/* ── AI SECTION ── */
.ai-section {
  background:linear-gradient(135deg,var(--navy2),var(--navy3));
  border-top:1px solid var(--border);
  border-bottom:1px solid var(--border);
}
.ai-inner {
  display:grid; grid-template-columns:1fr 1fr;
  gap:5rem; align-items:center;
}
.symptom-demo {
  background:var(--navy);
  border:1px solid var(--border);
  border-radius:24px;
  padding:2rem;
  box-shadow:0 30px 60px rgba(0,0,0,.4);
}
.symptom-input-wrap {
  display:flex; gap:.7rem; margin-bottom:1.5rem;
}
.symptom-input {
  flex:1; padding:.85rem 1.2rem;
  background:var(--white-ghost);
  border:1px solid var(--border);
  border-radius:12px;
  color:var(--white);
  font-family:'Syne',sans-serif;
  font-size:.9rem;
  outline:none;
  transition:.2s;
}
.symptom-input:focus { border-color:var(--teal); }
.symptom-btn {
  padding:.85rem 1.3rem;
  background:var(--teal); border:none;
  border-radius:12px; color:#fff;
  font-weight:600; cursor:pointer;
  font-size:.9rem; transition:.2s;
  white-space:nowrap;
}
.symptom-btn:hover { background:var(--teal2); }
.ai-result {
  background:var(--navy2);
  border:1px solid var(--border2);
  border-radius:14px;
  padding:1.2rem;
}
.ai-result-header {
  display:flex; align-items:center; gap:.5rem;
  font-size:.8rem; font-weight:600;
  color:var(--teal);
  margin-bottom:.8rem;
}
.ai-result-body {
  font-size:.87rem; color:var(--white-dim);
  line-height:1.65; font-weight:300;
}
.typing-dots span {
  display:inline-block; width:5px; height:5px;
  border-radius:50%; background:var(--teal);
  margin:0 2px;
  animation:dot-bounce .8s ease infinite;
}
.typing-dots span:nth-child(2){animation-delay:.15s;}
.typing-dots span:nth-child(3){animation-delay:.3s;}
@keyframes dot-bounce{0%,80%,100%{transform:translateY(0);}40%{transform:translateY(-5px);}}

/* ── TESTIMONIALS ── */
.testimonials-grid {
  display:grid; grid-template-columns:repeat(auto-fit,minmax(300px,1fr));
  gap:1.5rem; margin-top:4rem;
}
.testimonial {
  background:var(--navy2);
  border:1px solid var(--border);
  border-radius:20px;
  padding:2rem;
  transition:.3s;
}
.testimonial:hover {
  border-color:var(--border2);
  transform:translateY(-4px);
}
.testi-quote {
  font-size:.95rem; color:var(--white-dim);
  line-height:1.75; font-weight:300;
  margin-bottom:1.5rem;
  font-style:italic;
  font-family:'Playfair Display',serif;
}
.testi-author {
  display:flex; align-items:center; gap:.8rem;
}
.testi-avatar {
  width:40px; height:40px; border-radius:50%;
  background:linear-gradient(135deg,var(--teal),var(--teal2));
  display:flex; align-items:center; justify-content:center;
  font-size:.85rem; font-weight:700; color:#fff;
}
.testi-name { font-size:.9rem; font-weight:600; }
.testi-role { font-size:.78rem; color:var(--white-dim); }
.stars { color:var(--gold); font-size:.8rem; letter-spacing:2px; margin-bottom:1rem; }

/* ── CTA ── */
.cta-section {
  text-align:center;
  padding:8rem 5vw;
  background:var(--navy2);
  border-top:1px solid var(--border);
  position:relative; overflow:hidden;
}
.cta-section::before {
  content:'';
  position:absolute;
  top:-50%; left:50%; transform:translateX(-50%);
  width:800px; height:800px;
  background:radial-gradient(circle,rgba(31,189,140,.1),transparent 70%);
  pointer-events:none;
}
.cta-badge {
  display:inline-flex; align-items:center; gap:8px;
  padding:.45rem 1.1rem;
  background:var(--gold-dim);
  border:1px solid rgba(212,168,67,.25);
  border-radius:50px;
  font-size:.78rem; font-weight:600;
  letter-spacing:.6px; text-transform:uppercase;
  color:var(--gold);
  margin-bottom:2rem;
}
.cta-heading {
  font-family:'Playfair Display',serif;
  font-size:clamp(2.2rem,5vw,4rem);
  font-weight:700; line-height:1.1;
  letter-spacing:-.02em; margin-bottom:1.2rem;
}
.cta-sub {
  font-size:1rem; color:var(--white-dim);
  max-width:480px; margin:0 auto 2.5rem;
  line-height:1.75; font-weight:300;
}
.cta-actions { display:flex; justify-content:center; gap:1rem; flex-wrap:wrap; }

/* ── FOOTER ── */
footer {
  position:relative; z-index:1;
  padding:4rem 5vw 2.5rem;
  border-top:1px solid var(--border);
}
.footer-inner {
  display:grid; grid-template-columns:2fr 1fr 1fr 1fr;
  gap:3rem; margin-bottom:3rem;
}
.footer-brand p {
  font-size:.88rem; color:var(--white-dim);
  line-height:1.7; font-weight:300;
  margin-top:1rem; max-width:280px;
}
.footer-col-title {
  font-size:.8rem; font-weight:600;
  letter-spacing:.8px; text-transform:uppercase;
  color:var(--white); margin-bottom:1.2rem;
}
.footer-links { list-style:none; display:flex; flex-direction:column; gap:.7rem; }
.footer-links a {
  font-size:.87rem; color:var(--white-dim);
  text-decoration:none; font-weight:300;
  transition:.2s;
}
.footer-links a:hover { color:var(--teal); }
.footer-bottom {
  display:flex; align-items:center; justify-content:space-between;
  padding-top:2rem; border-top:1px solid var(--border);
  font-size:.8rem; color:var(--white-dim); flex-wrap:wrap; gap:1rem;
}

/* ── RESPONSIVE ── */
@media(max-width:1024px){
  .hero-visual { display:none; }
  .divisions-inner, .ai-inner { grid-template-columns:1fr; gap:3rem; }
  .footer-inner { grid-template-columns:1fr 1fr; }
}
@media(max-width:640px){
  nav { padding:1rem 4vw; }
  .nav-links { display:none; }
  section { padding:5rem 4vw; }
  .hero { padding:7rem 4vw 4rem; }
  .hero-stats { gap:2rem; }
  .footer-inner { grid-template-columns:1fr; }
}
</style>
</head>
<body>

<!-- NAV -->
<nav>
  <div class="nav-logo">
    <div class="nav-logo-mark">🩺</div>
    <div class="nav-logo-text">Pro<span>Clinic</span></div>
  </div>
  <ul class="nav-links">
    <li><a href="#features">Features</a></li>
    <li><a href="#divisions">Divisions</a></li>
    <li><a href="#how">How it works</a></li>
    <li><a href="#ai">AI Diagnosis</a></li>
    <li><a href="index.php" class="nav-cta">Get Started</a></li>
  </ul>
</nav>

<!-- HERO -->
<section class="hero">
  <div class="hero-orb hero-orb-1"></div>
  <div class="hero-orb hero-orb-2"></div>

  <div class="hero-inner">
    <div class="hero-badge">
      <div class="hero-badge-dot"></div>
      Now with AI Symptom Analysis
    </div>
    <h1 class="hero-headline">
      Your health,<br>our <em>priority</em>.
    </h1>
    <p class="hero-sub">
      Book appointments with top specialists, manage your health records, and get AI-powered symptom guidance — all from one beautiful platform.
    </p>
    <div class="hero-actions">
      <a href="index.php" class="btn-primary">
        Book an Appointment →
      </a>
      <a href="#how" class="btn-ghost">
        See how it works
      </a>
    </div>
    <div class="hero-stats">
      <div>
        <div class="hero-stat-num">8+</div>
        <div class="hero-stat-lbl">Specialties</div>
      </div>
      <div>
        <div class="hero-stat-num">24/7</div>
        <div class="hero-stat-lbl">Live Chat</div>
      </div>
      <div>
        <div class="hero-stat-num">AI</div>
        <div class="hero-stat-lbl">Diagnosis</div>
      </div>
    </div>
  </div>

  <!-- Floating card stack -->
  <div class="hero-visual">
    <div class="hero-card-stack">
      <!-- behind card -->
      <div class="hero-card hc-behind">
        <div class="card-dividers">
          <div class="card-divider"></div>
          <div class="card-divider"></div>
          <div class="card-divider"></div>
        </div>
      </div>
      <!-- main card -->
      <div class="hero-card hc-main">
        <div class="card-header">
          <div class="card-avatar">DR</div>
          <div>
            <div class="card-name">Dr. Amara Nkosi</div>
            <div class="card-role">Cardiologist · Kigali</div>
          </div>
        </div>
        <div class="card-dividers">
          <div class="card-divider" style="width:100%"></div>
          <div class="card-divider" style="width:80%"></div>
          <div class="card-divider" style="width:60%"></div>
        </div>
        <div class="card-status">
          <div class="card-status-dot"></div>
          Appointment Accepted
        </div>
      </div>
      <!-- front mini card -->
      <div class="hero-card hc-front">
        <div class="mini-label">This Week</div>
        <div class="mini-sublabel" style="margin-bottom:.5rem">Appointments</div>
        <div class="mini-chart">
          <div class="mini-bar" style="height:40%"></div>
          <div class="mini-bar active" style="height:75%"></div>
          <div class="mini-bar" style="height:55%"></div>
          <div class="mini-bar active" style="height:90%"></div>
          <div class="mini-bar" style="height:45%"></div>
          <div class="mini-bar" style="height:65%"></div>
          <div class="mini-bar active" style="height:80%"></div>
        </div>
        <div class="mini-sublabel">↑ 18% vs last week</div>
      </div>
    </div>
  </div>
</section>

<!-- MARQUEE -->
<div class="marquee-strip">
  <div class="marquee-track">
    <span class="marquee-item"><span class="marquee-sep"></span>Cardiology</span>
    <span class="marquee-item"><span class="marquee-sep"></span>Neurology</span>
    <span class="marquee-item"><span class="marquee-sep"></span>Orthopedics</span>
    <span class="marquee-item"><span class="marquee-sep"></span>Pulmonology</span>
    <span class="marquee-item"><span class="marquee-sep"></span>General Medicine</span>
    <span class="marquee-item"><span class="marquee-sep"></span>Gynecology</span>
    <span class="marquee-item"><span class="marquee-sep"></span>Dental</span>
    <span class="marquee-item"><span class="marquee-sep"></span>Pediatrics</span>
    <span class="marquee-item"><span class="marquee-sep"></span>AI Diagnosis</span>
    <span class="marquee-item"><span class="marquee-sep"></span>Live Doctor Chat</span>
    <span class="marquee-item"><span class="marquee-sep"></span>Cardiology</span>
    <span class="marquee-item"><span class="marquee-sep"></span>Neurology</span>
    <span class="marquee-item"><span class="marquee-sep"></span>Orthopedics</span>
    <span class="marquee-item"><span class="marquee-sep"></span>Pulmonology</span>
    <span class="marquee-item"><span class="marquee-sep"></span>General Medicine</span>
    <span class="marquee-item"><span class="marquee-sep"></span>Gynecology</span>
    <span class="marquee-item"><span class="marquee-sep"></span>Dental</span>
    <span class="marquee-item"><span class="marquee-sep"></span>Pediatrics</span>
    <span class="marquee-item"><span class="marquee-sep"></span>AI Diagnosis</span>
    <span class="marquee-item"><span class="marquee-sep"></span>Live Doctor Chat</span>
  </div>
</div>

<!-- FEATURES -->
<section id="features">
  <div class="section-label">Why ProClinic</div>
  <h2 class="section-heading">Everything you need<br>in one <em>place</em></h2>
  <p class="section-sub">From booking to diagnosis, ProClinic streamlines every step of your healthcare journey.</p>

  <div class="features-grid">
    <div class="feat-cell">
      <div class="feat-icon green">📅</div>
      <div class="feat-title">Instant Appointment Booking</div>
      <div class="feat-desc">Browse doctors by specialty, pick a date and time, and confirm your appointment in under a minute. No phone calls, no waiting.</div>
      <a href="index.php" class="feat-link">Book now →</a>
    </div>
    <div class="feat-cell">
      <div class="feat-icon gold">🤖</div>
      <div class="feat-title">AI Symptom Analysis</div>
      <div class="feat-desc">Describe your symptoms and our AI instantly suggests possible diagnoses and recommended medicines — helping you understand your health better.</div>
      <a href="index.php" class="feat-link">Try it →</a>
    </div>
    <div class="feat-cell">
      <div class="feat-icon green">💬</div>
      <div class="feat-title">Live Doctor Chat</div>
      <div class="feat-desc">Connect with a healthcare professional in real time. Get quick answers to medical questions without leaving your home.</div>
      <a href="index.php" class="feat-link">Start chat →</a>
    </div>
    <div class="feat-cell">
      <div class="feat-icon gold">📂</div>
      <div class="feat-title">8 Medical Divisions</div>
      <div class="feat-desc">Cardiology, Neurology, Orthopedics, Pediatrics, Dental, and more — every major specialty covered under one roof.</div>
      <a href="#divisions" class="feat-link">Explore →</a>
    </div>
    <div class="feat-cell">
      <div class="feat-icon green">🔒</div>
      <div class="feat-title">Secure Patient Portal</div>
      <div class="feat-desc">Your health data is encrypted and private. View appointment history, doctor notes, and status updates from your personal dashboard.</div>
      <a href="index.php" class="feat-link">Sign in →</a>
    </div>
    <div class="feat-cell">
      <div class="feat-icon gold">👨‍⚕️</div>
      <div class="feat-title">Doctor & Admin Portals</div>
      <div class="feat-desc">Dedicated dashboards for doctors to manage their schedules and for admins to oversee the full clinic — all in one system.</div>
      <a href="doctor_login.php" class="feat-link">Doctor login →</a>
    </div>
  </div>
</section>

<!-- DIVISIONS -->
<section id="divisions" class="divisions-section">
  <div class="divisions-inner">
    <div>
      <div class="section-label">Our Specialties</div>
      <h2 class="section-heading">All the care<br>you <em>need</em></h2>
      <p class="section-sub">Eight specialized medical divisions staffed by expert doctors, each with deep expertise in their field.</p>
      <div class="divisions-list">
        <div class="div-pill"><span class="div-emoji">❤️</span> Cardiology</div>
        <div class="div-pill"><span class="div-emoji">🧠</span> Neurology</div>
        <div class="div-pill"><span class="div-emoji">🦴</span> Orthopedics</div>
        <div class="div-pill"><span class="div-emoji">🫁</span> Pulmonology</div>
        <div class="div-pill"><span class="div-emoji">🏥</span> General Medicine</div>
        <div class="div-pill"><span class="div-emoji">👶</span> Gynecology</div>
        <div class="div-pill"><span class="div-emoji">🦷</span> Dental</div>
        <div class="div-pill"><span class="div-emoji">🧒</span> Pediatrics</div>
      </div>
    </div>
    <div class="divisions-visual">
      <div class="appt-mockup">
        <div class="appt-mockup-header">
          <div class="appt-mockup-title">📅 My Appointments</div>
          <button class="appt-mockup-btn">+ New</button>
        </div>
        <div class="appt-row">
          <div class="appt-row-dot" style="background:var(--teal)"></div>
          <div class="appt-info">
            <div class="appt-doctor">Dr. Amara Nkosi</div>
            <div class="appt-div">Cardiology · June 4, 10:00 AM</div>
          </div>
          <span class="appt-badge badge-accepted">Accepted</span>
        </div>
        <div class="appt-row">
          <div class="appt-row-dot" style="background:var(--gold)"></div>
          <div class="appt-info">
            <div class="appt-doctor">Dr. Claire Mugenzi</div>
            <div class="appt-div">Neurology · June 8, 2:30 PM</div>
          </div>
          <span class="appt-badge badge-pending">Pending</span>
        </div>
        <div class="appt-row">
          <div class="appt-row-dot" style="background:#8de08d"></div>
          <div class="appt-info">
            <div class="appt-doctor">Dr. Felix Habimana</div>
            <div class="appt-div">Pediatrics · May 22, 9:00 AM</div>
          </div>
          <span class="appt-badge badge-done">Done</span>
        </div>
        <div class="appt-row">
          <div class="appt-row-dot" style="background:var(--teal)"></div>
          <div class="appt-info">
            <div class="appt-doctor">Dr. Sandra Uwera</div>
            <div class="appt-div">Dental · June 12, 11:00 AM</div>
          </div>
          <span class="appt-badge badge-accepted">Accepted</span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- HOW IT WORKS -->
<section id="how">
  <div style="text-align:center">
    <div class="section-label" style="justify-content:center">Simple Process</div>
    <h2 class="section-heading">Three steps to better <em>care</em></h2>
    <p class="section-sub" style="margin:0 auto">Getting the care you need has never been simpler. ProClinic makes healthcare accessible for everyone.</p>
  </div>
  <div class="steps">
    <div class="step">
      <div class="step-num">1</div>
      <div class="step-title">Create your account</div>
      <div class="step-desc">Register in seconds. Enter your name, email, and location. No paperwork, no waiting room queues.</div>
    </div>
    <div class="step">
      <div class="step-num">2</div>
      <div class="step-title">Choose a doctor</div>
      <div class="step-desc">Browse by medical division, view doctors' specialties, and select an available time that works for you.</div>
    </div>
    <div class="step">
      <div class="step-num">3</div>
      <div class="step-title">Get treated</div>
      <div class="step-desc">Attend your appointment. Your doctor reviews your case, provides notes, and you track everything in your portal.</div>
    </div>
    <div class="step">
      <div class="step-num">4</div>
      <div class="step-title">Follow up anytime</div>
      <div class="step-desc">Use live chat for quick questions, check AI symptom analysis, or rebook with a specialist — all in one place.</div>
    </div>
  </div>
</section>

<!-- AI SECTION -->
<section id="ai" class="ai-section">
  <div class="ai-inner">
    <div>
      <div class="section-label">Powered by AI</div>
      <h2 class="section-heading">Describe symptoms,<br>get <em>answers</em></h2>
      <p class="section-sub">Our AI system analyzes your described symptoms and suggests potential diagnoses and medications — helping you prepare before seeing your doctor.</p>
      <ul style="margin-top:2rem;display:flex;flex-direction:column;gap:.8rem;list-style:none;">
        <li style="display:flex;align-items:center;gap:.7rem;font-size:.9rem;color:var(--white-dim);">
          <span style="color:var(--teal);font-weight:700">✓</span> Instant symptom analysis
        </li>
        <li style="display:flex;align-items:center;gap:.7rem;font-size:.9rem;color:var(--white-dim);">
          <span style="color:var(--teal);font-weight:700">✓</span> Suggested medicines & treatments
        </li>
        <li style="display:flex;align-items:center;gap:.7rem;font-size:.9rem;color:var(--white-dim);">
          <span style="color:var(--teal);font-weight:700">✓</span> Recommended medical division
        </li>
        <li style="display:flex;align-items:center;gap:.7rem;font-size:.9rem;color:var(--white-dim);">
          <span style="color:var(--teal);font-weight:700">✓</span> Seamlessly book a related specialist
        </li>
      </ul>
    </div>
    <div>
      <div class="symptom-demo">
        <div style="font-size:.85rem;font-weight:600;color:var(--white-dim);margin-bottom:1rem;letter-spacing:.3px;text-transform:uppercase;font-size:.75rem;">🩺 AI Symptom Checker</div>
        <div class="symptom-input-wrap">
          <input type="text" class="symptom-input" id="demoInput" placeholder="e.g. chest pain, shortness of breath…" value="severe headache and fever"/>
          <button class="symptom-btn" onclick="runDemo()">🔍 Check</button>
        </div>
        <div class="ai-result" id="aiResult">
          <div class="ai-result-header">
            <span style="color:var(--teal)">🤖</span> AI Analysis
          </div>
          <div class="ai-result-body" id="aiResultBody">
            Based on <strong style="color:var(--white)">severe headache and fever</strong>, possible conditions include: <strong style="color:var(--teal)">viral fever, meningitis, or sinusitis</strong>. Recommended division: <strong style="color:var(--white)">General Medicine or Neurology</strong>. Suggested care: paracetamol for fever management, hydration, and urgent evaluation if stiff neck or photophobia is present.
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- TESTIMONIALS -->
<section>
  <div style="text-align:center">
    <div class="section-label" style="justify-content:center">Patient Stories</div>
    <h2 class="section-heading">Trusted by <em>patients</em></h2>
  </div>
  <div class="testimonials-grid">
    <div class="testimonial">
      <div class="stars">★★★★★</div>
      <div class="testi-quote">"I booked a cardiologist appointment in literally two minutes. The system accepted it immediately and the doctor's notes were in my portal the same day."</div>
      <div class="testi-author">
        <div class="testi-avatar">AM</div>
        <div>
          <div class="testi-name">Amina Mutoni</div>
          <div class="testi-role">Patient · Kigali</div>
        </div>
      </div>
    </div>
    <div class="testimonial">
      <div class="stars">★★★★★</div>
      <div class="testi-quote">"The AI symptom checker told me I likely had sinusitis before I even saw the doctor. He confirmed it and said the AI was spot on. Incredible technology."</div>
      <div class="testi-author">
        <div class="testi-avatar">JN</div>
        <div>
          <div class="testi-name">Jean-Pierre Nzabonimpa</div>
          <div class="testi-role">Patient · Musanze</div>
        </div>
      </div>
    </div>
    <div class="testimonial">
      <div class="stars">★★★★★</div>
      <div class="testi-quote">"As a busy mom, I love that I can manage my kids' pediatric appointments and my own dental bookings from one account. It saves me so much time."</div>
      <div class="testi-author">
        <div class="testi-avatar">GU</div>
        <div>
          <div class="testi-name">Grace Uwimana</div>
          <div class="testi-role">Patient · Butare</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="cta-section">
  <div class="cta-badge">🌟 Free to join</div>
  <h2 class="cta-heading">Start your health journey<br>today</h2>
  <p class="cta-sub">Create a free patient account and book your first appointment with a specialist in minutes.</p>
  <div class="cta-actions">
    <a href="index.php" class="btn-primary">Create Free Account →</a>
    <a href="doctor_login.php" class="btn-ghost">I'm a Doctor</a>
  </div>
</section>

<!-- FOOTER -->
<footer>
  <div class="footer-inner">
    <div class="footer-brand">
      <div class="nav-logo">
        <div class="nav-logo-mark">🩺</div>
        <div class="nav-logo-text">Pro<span>Clinic</span></div>
      </div>
      <p>A modern healthcare platform connecting patients with top specialists across Rwanda and East Africa.</p>
    </div>
    <div>
      <div class="footer-col-title">Platform</div>
      <ul class="footer-links">
        <li><a href="index.php">Patient Login</a></li>
        <li><a href="index.php">Create Account</a></li>
        <li><a href="doctor_login.php">Doctor Portal</a></li>
        <li><a href="admin_login.php">Admin Portal</a></li>
      </ul>
    </div>
    <div>
      <div class="footer-col-title">Specialties</div>
      <ul class="footer-links">
        <li><a href="index.php">Cardiology</a></li>
        <li><a href="index.php">Neurology</a></li>
        <li><a href="index.php">Pediatrics</a></li>
        <li><a href="index.php">General Medicine</a></li>
      </ul>
    </div>
    <div>
      <div class="footer-col-title">Support</div>
      <ul class="footer-links">
        <li><a href="#">Live Chat</a></li>
        <li><a href="#">Help Center</a></li>
        <li><a href="#">Privacy Policy</a></li>
        <li><a href="#">Terms of Service</a></li>
      </ul>
    </div>
  </div>
  <div class="footer-bottom">
    <span>© 2026 ProClinic Health Platform. All rights reserved.</span>
    <span>Made with ❤️ for better healthcare</span>
  </div>
</footer>

<script>
function runDemo() {
  const input = document.getElementById('demoInput').value.trim();
  if (!input) return;
  const body = document.getElementById('aiResultBody');
  body.innerHTML = '<div class="typing-dots"><span></span><span></span><span></span></div>';
  setTimeout(() => {
    const responses = {
      default: `Based on <strong style="color:var(--white)">${input}</strong>, our AI suggests consulting a specialist. Please use the booking system to schedule an appointment.`
    };
    const lower = input.toLowerCase();
    let resp = responses.default;
    if (lower.includes('chest') || lower.includes('heart')) {
      resp = `Based on <strong style="color:var(--white)">${input}</strong>, possible conditions: <strong style="color:var(--teal)">angina, pericarditis, or anxiety</strong>. Recommended division: <strong style="color:var(--white)">Cardiology</strong>. Seek urgent care if pain radiates to the arm or jaw.`;
    } else if (lower.includes('headache') || lower.includes('fever')) {
      resp = `Based on <strong style="color:var(--white)">${input}</strong>, possible conditions: <strong style="color:var(--teal)">viral fever, sinusitis, or migraine</strong>. Recommended: <strong style="color:var(--white)">General Medicine or Neurology</strong>. Rest, hydration, and paracetamol recommended.`;
    } else if (lower.includes('cough') || lower.includes('breath')) {
      resp = `Based on <strong style="color:var(--white)">${input}</strong>, possible conditions: <strong style="color:var(--teal)">asthma, bronchitis, or respiratory infection</strong>. Recommended division: <strong style="color:var(--white)">Pulmonology</strong>. Avoid cold air, stay hydrated.`;
    } else if (lower.includes('joint') || lower.includes('bone') || lower.includes('knee')) {
      resp = `Based on <strong style="color:var(--white)">${input}</strong>, possible conditions: <strong style="color:var(--teal)">arthritis, sprain, or injury</strong>. Recommended division: <strong style="color:var(--white)">Orthopedics</strong>. Rest and anti-inflammatory medication may help.`;
    } else if (lower.includes('tooth') || lower.includes('teeth') || lower.includes('gum')) {
      resp = `Based on <strong style="color:var(--white)">${input}</strong>, possible conditions: <strong style="color:var(--teal)">cavity, gingivitis, or abscess</strong>. Recommended division: <strong style="color:var(--white)">Dental</strong>. Saltwater rinse and prompt dental evaluation advised.`;
    }
    body.innerHTML = resp;
  }, 1200);
}
</script>

</body>
</html>