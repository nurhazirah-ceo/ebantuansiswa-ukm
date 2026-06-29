<!DOCTYPE html>
<html lang="ms">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>eBantuanSiswa UKM</title>

  <!-- Poppins Font -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  @vite(['resources/css/app.css', 'resources/js/app.js'])

<style>
:root {
  --navy: #071a36;
  --blue: #2563eb;
  --blue-dark: #1d4ed8;
  --sky: #38bdf8;
  --cyan: #06b6d4;
  --yellow: #facc15;
  --soft-blue: #eff6ff;
  --text-dark: #0f172a;
  --text-muted: #475569;
  --border: #dbeafe;
}

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

html { scroll-behavior: smooth; }

body {
  font-family: "Poppins", sans-serif;
  background: #ffffff;
  color: var(--text-dark);
}

/* NAVBAR */
.navbar {
  position: fixed;
  top: 0;
  width: 100%;
  height: 64px;
  padding: 0 50px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: rgba(255,255,255,0.9);
  backdrop-filter: blur(18px);
  border-bottom: 1px solid rgba(37,99,235,0.14);
  z-index: 100;
  animation: navDrop 0.8s ease forwards;
}

.logo {
  font-size: 14px;
  font-weight: 800;
  letter-spacing: 0.5px;
  color: var(--blue-dark);
}

.nav-links {
  display: flex;
  align-items: center;
  gap: 42px;
}

.nav-links a {
  position: relative;
  color: #334155;
  text-decoration: none;
  font-size: 13px;
  font-weight: 800;
  letter-spacing: 2px;
  text-transform: uppercase;
  padding: 8px 0;
  transition: 0.3s ease;
}

.nav-links a::after {
  content: "";
  position: absolute;
  left: 50%;
  bottom: -8px;
  width: 0;
  height: 3px;
  background: linear-gradient(90deg, var(--blue), var(--sky));
  border-radius: 999px;
  transform: translateX(-50%);
  transition: 0.3s ease;
}

.nav-links a:hover,
.nav-links a.active {
  color: var(--blue-dark);
}

.nav-links a:hover::after,
.nav-links a.active::after {
  width: 100%;
}

.nav-actions {
  display: flex;
  gap: 12px;
  font-size: 11px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.login-btn {
  border: 1px solid rgba(37,99,235,0.35);
  color: var(--blue-dark);
  padding: 9px 18px;
  border-radius: 10px;
  text-decoration: none;
  transition: 0.3s ease;
}

.login-btn:hover {
  background: var(--soft-blue);
  transform: translateY(-2px);
}

.start-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, var(--blue), var(--sky));
  color: white;
  padding: 10px 19px;
  border-radius: 10px;
  border: 0;
  text-decoration: none;
  font-weight: 800;
  font-family: inherit;
  cursor: pointer;
  transition: 0.3s ease;
  box-shadow: 0 12px 28px rgba(37,99,235,0.28);
}

.start-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 18px 38px rgba(37,99,235,0.34);
}

/* HERO */
.hero {
  position: relative;
  overflow: hidden;
  min-height: 100vh;
  padding-top: 64px;
  display: flex;
  align-items: center;
  background: none;
}

.hero::before {
  content: "";
  position: absolute;
  inset: 0;
  background:
    linear-gradient(
      90deg,
      rgba(239,246,255,0.72) 0%,
      rgba(224,242,254,0.58) 25%,
      rgba(186,230,253,0.32) 45%,
      rgba(255,255,255,0.10) 65%,
      rgba(255,255,255,0) 100%
    ),
    url("/image/ui/ukm.jpg");
  background-size: cover;
  background-position: center;
  z-index: 0;
  animation: heroMove 18s ease-in-out infinite alternate;
}

.hero::after {
  content: "";
  position: absolute;
  inset: 0;
  background:
    radial-gradient(circle at 20% 35%, rgba(56,189,248,0.34), transparent 28%),
    radial-gradient(circle at 38% 70%, rgba(250,204,21,0.18), transparent 24%),
    radial-gradient(circle at 70% 20%, rgba(37,99,235,0.18), transparent 32%);
  z-index: 0;
  animation: heroGlow 7s ease-in-out infinite alternate;
}

.hero-bottom-fade {
    position: absolute;

    bottom: 0;

    left: 0;

    width: 100%;

    height: 160px;

    background:
        linear-gradient(
            to bottom,
            transparent,
            #f8fbff
        );

    z-index: 1;
}

.hero-content {
  width: min(1200px, 90%);
  height: calc(100vh - 64px);
  margin: auto;
  display: flex;
  align-items: center;
  position: relative;
  z-index: 2;
}

.hero-text {
  max-width: 540px;
  margin-left: 40px;
}

.hero h1 {
  font-size: clamp(30px, 4vw, 52px);
  line-height: 1.1;
  font-weight: 800;
  margin-bottom: 24px;
  color: #071a36;
  letter-spacing: -1px;
  text-shadow: 0 2px 10px rgba(255,255,255,0.45);
  opacity: 0;
  animation: slideInLeft 1s ease forwards;
}

.hero p {
  font-size: 16px;
  font-weight: 500;
  color: #334155;
  line-height: 1.75;
  margin-bottom: 34px;
  text-shadow: 0 1px 6px rgba(255,255,255,0.35);
  opacity: 0;
  animation: slideInLeft 1s ease forwards;
  animation-delay: 0.25s;
}

.hero-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, var(--blue), var(--cyan));
  color: white;
  padding: 15px 32px;
  font-size: 13px;
  font-weight: 800;
  border-radius: 14px;
  text-decoration: none;
  transition: 0.3s ease;
  box-shadow: 0 16px 36px rgba(37,99,235,0.30);
  opacity: 0;
  animation: buttonPop 0.8s ease forwards;
  animation-delay: 0.55s;
}

.hero-btn:hover {
  transform: translateY(-4px);
  box-shadow: 0 22px 48px rgba(37,99,235,0.38);
}

/* BANTUAN SECTION */
.bantuan-section {
  background: #f6f8fc;
  color: #071a36;
  padding: 95px 0;
}

.bantuan-container {
  width: min(1200px, 90%);
  margin: auto;
}

.section-header {
  text-align: center;
  max-width: 760px;
  margin: 0 auto 55px;
}

.section-header span {
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 2px;
  color: #2563eb;
}

.section-header h2 {
  font-size: clamp(32px, 4vw, 48px);
  line-height: 1.15;
  margin: 12px 0 14px;
  font-weight: 700;
}

.section-header p {
  color: #64748b;
  font-size: 15px;
  line-height: 1.7;
}

.bantuan-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 28px;
}

.bantuan-card {
  background: white;
  border-radius: 26px;
  overflow: hidden;
  box-shadow: 0 22px 55px rgba(7,26,54,0.12);
  border: 1px solid rgba(226,232,240,0.9);
  transition: 0.3s ease;
}

.bantuan-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 28px 70px rgba(7,26,54,0.18);
}

.bantuan-img {
  height: 240px;
  padding: 26px;
  background: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
}

.bantuan-img img {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.bantuan-content {
  padding: 28px;
}

.tag {
  display: inline-block;
  background: #dbeafe;
  color: #1d4ed8;
  padding: 6px 12px;
  border-radius: 999px;
  font-size: 11px;
  font-weight: 700;
  margin-bottom: 14px;
}

.bantuan-content h3 {
  font-size: 24px;
  font-weight: 600;
  margin-bottom: 10px;
  color: #071a36;
}

.bantuan-content p {
  color: #64748b;
  font-size: 14px;
  line-height: 1.7;
  margin-bottom: 24px;
}

.bantuan-content a:not(.start-btn),
.bantuan-content button:not(.start-btn) {
  display: block;
  width: 100%;
  text-align: center;
  background: #071a36;
  color: white;
  padding: 13px;
  border-radius: 8px;
  border: 0;
  text-decoration: none;
  font-size: 13px;
  font-weight: 600;
  font-family: inherit;
  cursor: pointer;
  transition: 0.3s;
}

.bantuan-content a:not(.start-btn):hover,
.bantuan-content button:not(.start-btn):hover {
  background: #0b2447;
}

.bantuan-content .bantuan-home-action {
  width: 100%;
  min-height: 48px;
  font-size: 13px;
}

.home-bantuan-modal {
  position: fixed;
  inset: 0;
  z-index: 200;
  display: none;
  align-items: center;
  justify-content: center;
  padding: 24px;
}

.home-bantuan-modal.active {
  display: flex;
}

.home-bantuan-overlay {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  border: 0;
  background: rgba(7, 26, 54, 0.62);
  cursor: pointer;
}

.home-bantuan-dialog {
  position: relative;
  z-index: 1;
  width: min(680px, 100%);
  max-height: min(86vh, 760px);
  overflow-y: auto;
  background: #ffffff;
  border-radius: 22px;
  box-shadow: 0 32px 90px rgba(7, 26, 54, 0.28);
  border: 1px solid rgba(219, 234, 254, 0.95);
}

.home-bantuan-header {
  display: flex;
  justify-content: space-between;
  gap: 20px;
  padding: 26px 28px 18px;
  border-bottom: 1px solid #e2e8f0;
}

.home-bantuan-title {
  margin: 0;
  color: #071a36;
  font-size: 24px;
  font-weight: 800;
  line-height: 1.2;
}

.home-bantuan-subtitle {
  margin-top: 8px;
  color: #64748b;
  font-size: 14px;
  line-height: 1.6;
}

.home-bantuan-close {
  flex: 0 0 auto;
  width: 40px;
  height: 40px;
  border: 1px solid #dbeafe;
  border-radius: 12px;
  background: #f8fbff;
  color: #071a36;
  font-size: 16px;
  font-weight: 800;
  cursor: pointer;
  transition: 0.25s ease;
}

.home-bantuan-close:hover {
  background: #eff6ff;
}

.home-bantuan-body {
  padding: 24px 28px 28px;
}

.home-bantuan-panel {
  display: none;
}

.home-bantuan-panel.active {
  display: block;
}

.home-bantuan-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
}

.home-bantuan-item {
  min-height: 48px;
  display: flex;
  align-items: center;
  border: 1px solid #dbeafe;
  border-radius: 14px;
  background: #f8fbff;
  color: #1e293b;
  padding: 12px 14px;
  font-size: 14px;
  font-weight: 700;
}

.home-bantuan-note {
  margin-top: 18px;
  border: 1px solid #bfdbfe;
  border-radius: 14px;
  background: #eff6ff;
  color: #1e40af;
  padding: 14px 16px;
  font-size: 14px;
  line-height: 1.6;
}

.home-bantuan-actions {
  margin-top: 22px;
  display: flex;
  justify-content: center;
}

.home-bantuan-apply {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 240px;
  min-height: 46px;
  border-radius: 12px;
  background: linear-gradient(135deg, var(--blue) 0%, var(--sky) 100%);
  color: #ffffff;
  padding: 0 22px;
  text-decoration: none;
  font-size: 14px;
  font-weight: 800;
  box-shadow: 0 12px 28px rgba(37,99,235,0.28);
  transition: 0.3s ease;
}

.home-bantuan-apply:hover {
  transform: translateY(-2px);
  filter: brightness(1.06);
  box-shadow: 0 18px 38px rgba(37,99,235,0.34);
}

.home-bantuan-tabs {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 8px;
  margin-bottom: 18px;
  border-radius: 14px;
  background: #eef4ff;
  padding: 6px;
}

.home-bantuan-tab {
  min-height: 44px;
  border: 0;
  border-radius: 10px;
  background: transparent;
  color: #475569;
  padding: 8px 12px;
  font-family: inherit;
  font-size: 13px;
  font-weight: 800;
  cursor: pointer;
  transition: 0.25s ease;
}

.home-bantuan-tab.active {
  background: #ffffff;
  color: #1d4ed8;
  box-shadow: 0 8px 18px rgba(37, 99, 235, 0.12);
}

.home-bantuan-tab-panel {
  display: none;
}

.home-bantuan-tab-panel.active {
  display: block;
}

@media (max-width: 640px) {
  .home-bantuan-modal {
    padding: 16px;
  }

  .home-bantuan-header,
  .home-bantuan-body {
    padding-left: 20px;
    padding-right: 20px;
  }

  .home-bantuan-grid,
  .home-bantuan-tabs {
    grid-template-columns: 1fr;
  }

  .home-bantuan-actions {
    justify-content: center;
  }

  .home-bantuan-apply {
    width: min(100%, 240px);
  }
}

/* SYARAT SECTION */
.syarat-section {
  background:
    radial-gradient(circle at 0% 15%, rgba(255,221,120,0.20), transparent 28%),
    radial-gradient(circle at 100% 80%, rgba(37,99,235,0.10), transparent 30%),
    #f7f9fd;
  color: #071a36;
  padding: 95px 0;
}

.syarat-container {
  width: min(1200px, 90%);
  margin: auto;
}

.syarat-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 28px;
  align-items: stretch;
}

.syarat-card {
  background: rgba(255,255,255,0.92);
  border: 1px solid rgba(226,232,240,0.95);
  border-radius: 28px;
  padding: 34px;
  box-shadow: 0 24px 60px rgba(7,26,54,0.10);
  display: flex;
  flex-direction: column;
}

.card-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
  margin-bottom: 28px;
}

.card-top h3 {
  font-size: 23px;
  font-weight: 700;
}

.card-top span {
  color: #475569;
  border: 1px solid #dbe3ef;
  padding: 6px 14px;
  border-radius: 999px;
  font-size: 12px;
}

.wajib-list,
.tab-content ul {
  list-style: none;
}

.wajib-list li {
  position: relative;
  padding-left: 38px;
  color: #334155;
  font-size: 15px;
  line-height: 1.7;
  margin-bottom: 15px;
}

.wajib-list li::before {
  content: "✔";
  position: absolute;
  left: 0;
  top: 1px;
  color: #6d5bd0;
  font-weight: 900;
  font-size: 18px;
}

.tab-buttons {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-bottom: 26px;
}

.tab-btn {
  border: 1px solid #dbe3ef;
  background: white;
  color: #1f2937;
  padding: 11px 18px;
  border-radius: 13px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: 0.25s ease;
}

.tab-btn.active {
  background: #071a36;
  color: white;
  border-color: #071a36;
}

.tab-content {
  display: none;
}

.tab-content.active {
  display: block;
}

.tab-content li {
  position: relative;
  padding-left: 26px;
  color: #334155;
  font-size: 15px;
  line-height: 1.8;
  margin-bottom: 12px;
}

.tab-content li::before {
  content: "•";
  position: absolute;
  left: 0;
  color: #4f46e5;
  font-size: 22px;
  line-height: 1.2;
}

.syarat-link {
  display: inline-block;
  margin-top: auto;
  padding-top: 30px;
  color: #1f2937;
  font-size: 14px;
  font-weight: 700;
  text-decoration: none;
}

.syarat-link:hover {
  color: #2563eb;
}

/* PENDERMA SECTION */
.penderma-section {
  position: relative;
  background:
    radial-gradient(circle at 15% 15%, rgba(56,189,248,0.18), transparent 28%),
    radial-gradient(circle at 85% 80%, rgba(37,99,235,0.10), transparent 30%),
    radial-gradient(circle at 50% 100%, rgba(250,204,21,0.10), transparent 35%),
    linear-gradient(180deg, #f8fbff 0%, #eff6ff 50%, #ffffff 100%);
  color: #071a36;
  padding: 110px 0 90px;
  overflow: hidden;
}

.penderma-wrapper {
  width: min(1200px, 90%);
  margin: auto;
}

.penderma-heading {
  text-align: center;
  margin-bottom: 60px;
}

.penderma-heading span {
  display: inline-block;
  color: #2563eb;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 3px;
  margin-bottom: 18px;
}

.penderma-heading h2 {
  width: fit-content;
  margin: 0 auto 24px;
  font-size: clamp(36px, 4vw, 58px);
  line-height: 1.1;
  font-weight: 700;
  color: #071a36;
  white-space: nowrap;
}

.penderma-heading p {
  color: #64748b;
  font-size: 16px;
  line-height: 1.9;
  max-width: 950px;
  margin: 0 auto;
}

.logo-slider {
  --donor-ticker-card: 260px;
  --donor-ticker-height: 120px;
  --donor-ticker-gap: 32px;
  width: min(1200px, 100%);
  overflow: hidden;
  margin: 50px auto 0;
  -webkit-mask-image: linear-gradient(90deg, transparent 0%, #000 7%, #000 93%, transparent 100%);
  mask-image: linear-gradient(90deg, transparent 0%, #000 7%, #000 93%, transparent 100%);
}

.logo-track {
  display: flex;
  width: max-content;
  animation: donorTicker 28s linear infinite;
  will-change: transform;
}

.logo-sequence {
  display: flex;
  flex: 0 0 auto;
  gap: var(--donor-ticker-gap);
  padding-right: var(--donor-ticker-gap);
}

.logo-track:hover {
  animation-play-state: paused;
}

.logo-item {
  flex: 0 0 var(--donor-ticker-card);
  width: var(--donor-ticker-card);
  height: var(--donor-ticker-height);
  background: rgba(255,255,255,0.92);
  border: 1px solid rgba(219,234,254,0.9);
  border-radius: 22px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 12px 35px rgba(37,99,235,0.10);
  overflow: hidden;
  transition: 0.3s ease;
}

.logo-item:hover {
  transform: translateY(-4px);
  box-shadow: 0 18px 45px rgba(37,99,235,0.16);
}

.logo-item img {
  max-width: 160px;
  max-height: 80px;
  object-fit: contain;
  transition: 0.3s ease;
}

.logo-item:hover img {
  transform: scale(1.06);
}

.logo-avatar {
  width: 74px;
  height: 74px;
  border-radius: 999px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #dbeafe, #bfdbfe);
  color: #1d4ed8;
  font-size: 28px;
  font-weight: 800;
  letter-spacing: 0;
}

.logo-avatar--backup {
  display: none;
}

/* FINAL CTA */
.final-cta {
  position: relative;
  overflow: hidden;
  padding: 90px 0;
  background:
    radial-gradient(circle at 15% 15%, rgba(147,197,253,0.13), transparent 28%),
    radial-gradient(circle at 85% 75%, rgba(59,130,246,0.10), transparent 30%),
    #06283D;
  border-top: 1px solid rgba(255,255,255,0.06);
}

.final-cta-container {
  width: min(1200px, 90%);
  margin: auto;
  display: grid;
  grid-template-columns: 1.2fr 1fr 1fr;
  gap: 60px;
}

.cta-left h2 {
  font-size: clamp(34px, 4vw, 52px);
  line-height: 1.08;
  font-weight: 700;
  color: white;
  margin-bottom: 16px;
}

.cta-left p,
.cta-contact p {
  color: rgba(255,255,255,0.68);
  font-size: 14px;
  line-height: 1.8;
}

.cta-left p {
  max-width: 420px;
}

.cta-links {
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.cta-links a {
  position: relative;
  width: fit-content;
  color: white;
  text-decoration: none;
  font-size: 18px;
  font-weight: 600;
  transition: 0.3s ease;
}

.cta-links a::after {
  content: "";
  position: absolute;
  left: 0;
  bottom: -6px;
  width: 0;
  height: 2px;
  background: #93c5fd;
  transition: 0.3s ease;
}

.cta-links a:hover {
  color: #dbeafe;
  transform: translateX(4px);
}

.cta-links a:hover::after {
  width: 100%;
}

.cta-contact {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.cta-contact h4 {
  color: white;
  font-size: 20px;
  font-weight: 700;
  margin-bottom: 8px;
}

/* RESPONSIVE */
@media (max-width: 900px) {
  .syarat-grid,
  .final-cta-container {
    grid-template-columns: 1fr;
  }

  .syarat-card {
    padding: 26px;
  }

  .card-top {
    align-items: flex-start;
    flex-direction: column;
  }

  .penderma-section {
    padding: 85px 0 70px;
  }

  .penderma-heading h2 {
    white-space: normal;
    font-size: 42px;
  }

  .logo-slider {
    --donor-ticker-card: 220px;
    --donor-ticker-height: 108px;
    --donor-ticker-gap: 24px;
  }

  .logo-item img {
    max-width: 130px;
    max-height: 70px;
  }

  .logo-avatar {
    width: 64px;
    height: 64px;
    font-size: 24px;
  }

  .final-cta {
    padding: 70px 0;
  }

  .final-cta-container {
    gap: 45px;
  }

  .cta-left h2 {
    font-size: 40px;
  }

  .cta-links a {
    font-size: 16px;
  }
}

@media (max-width: 640px) {
  .logo-slider {
    --donor-ticker-card: 190px;
    --donor-ticker-height: 96px;
    --donor-ticker-gap: 18px;
    margin-top: 34px;
    -webkit-mask-image: linear-gradient(90deg, transparent 0%, #000 10%, #000 90%, transparent 100%);
    mask-image: linear-gradient(90deg, transparent 0%, #000 10%, #000 90%, transparent 100%);
  }

  .logo-track {
    animation-duration: 24s;
  }

  .logo-item {
    border-radius: 18px;
  }

  .logo-item img {
    max-width: 112px;
    max-height: 58px;
  }

  .logo-avatar {
    width: 56px;
    height: 56px;
    font-size: 22px;
  }
}

@media (max-width: 768px) {
  .navbar {
    height: 60px;
    padding: 0 20px;
  }

  .nav-links {
    display: none;
  }

  .nav-actions {
    gap: 8px;
    font-size: 10px;
  }

  .login-btn,
  .start-btn {
    padding: 8px 12px;
  }

  .hero {
    padding-top: 60px;
  }

  .hero-content {
    height: calc(100vh - 60px);
  }

  .hero-text {
    margin-left: 0;
  }

  .hero h1 {
    font-size: 38px;
  }

  .hero p {
    font-size: 14px;
  }

  .bantuan-grid {
    grid-template-columns: 1fr;
  }

  .bantuan-img {
    height: 220px;
  }
}

/* ANIMATION */
@keyframes donorTicker {
  from { transform: translateX(0); }
  to { transform: translateX(-50%); }
}

@keyframes slideInLeft {
  from {
    opacity: 0;
    transform: translateX(-40px);
  }

  to {
    opacity: 1;
    transform: translateX(0);
  }
}

@keyframes buttonPop {
  from {
    opacity: 0;
    transform: scale(0.92);
  }

  to {
    opacity: 1;
    transform: scale(1);
  }
}

@keyframes navDrop {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes heroMove {
  from { transform: scale(1); }
  to { transform: scale(1.06); }
}

@keyframes heroGlow {
  from { opacity: 0.75; }
  to { opacity: 1; }
}
</style>
</head>

<body>

  <nav class="navbar">
    <div class="logo">eBantuanSiswa UKM</div>

    <div class="nav-links">
      <a href="#">Tentang</a>
      <a href="#bantuan">Bantuan</a>
      <a href="#syarat">Syarat</a>
      <a href="#penderma">Sokongan</a>
    </div>

    <div class="nav-actions">
      <a href="/login" class="login-btn">Log Masuk</a>
      <a href="{{ route('register') }}" class="start-btn">Daftar Masuk</a>
    </div>
  </nav>

  <section class="hero">
    <div class="hero-content">
      <div class="hero-text">
        <h1>Bantuan pelajar kini lebih mudah.</h1>

        <p>
          eBantuanSiswa UKM memudahkan pelajar memohon bantuan barangan,
          memuat naik dokumen serta menyemak status secara dalam talian.
        </p>

        <a href="/login" class="hero-btn">Mohon Sekarang →</a>
        
      </div>
    </div>
     <div class="hero-bottom-fade"></div>
  </section>

 
<section id="penderma" class="penderma-section">
  <div class="penderma-wrapper">

    <div class="penderma-heading">

    <span>SOKONGAN KOMUNITI</span>

    <h2>
        Terima kasih kepada para penyumbang.
    </h2>

    <p>
        Kami amat menghargai sokongan daripada para penderma yang telah
        menyumbang kepada kejayaan program bantuan ini. Terima kasih kerana
        membantu kami meringankan beban pelajar yang memerlukan.
    </p>

</div>

    @php
      $defaultDonorLogos = [
        ['name' => 'Petronas', 'src' => asset('image/homepage/petronas.jpg')],
        ['name' => 'Food Aid', 'src' => asset('image/homepage/foundation.jpg')],
        ['name' => 'Alumni UKM', 'src' => asset('image/homepage/alumni.jpg')],
      ];
    @endphp

    <div class="logo-slider" aria-label="Penyumbang komuniti">
      <div class="logo-track">

        @for ($sequenceIndex = 0; $sequenceIndex < 2; $sequenceIndex++)
          <div class="logo-sequence" aria-hidden="{{ $sequenceIndex === 1 ? 'true' : 'false' }}">
            @forelse ($homepageDonors as $donor)
              @php
                $donorName = optional($donor->user)->name ?? 'Penderma';
                $donorInitial = strtoupper(substr(trim($donorName), 0, 1)) ?: 'P';
                $donorLogoUrl = null;

                if ($donor->logo) {
                  $donorLogoFilename = basename(str_replace('\\', '/', (string) $donor->logo));
                  $publicDonorLogoPath = 'image/donors/' . $donorLogoFilename;

                  if ($donorLogoFilename !== '' && file_exists(public_path($publicDonorLogoPath))) {
                    $donorLogoUrl = asset($publicDonorLogoPath);
                  } elseif (\Illuminate\Support\Facades\Storage::disk('public')->exists($donor->logo)) {
                    $donorLogoUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($donor->logo);
                  }
                }

                $donorLogoExists = $donorLogoUrl !== null;
              @endphp
              <div class="logo-item">
                <span class="logo-avatar {{ $donorLogoExists ? 'logo-avatar--backup' : '' }}">{{ $donorInitial }}</span>
                @if ($donorLogoExists)
                  <img
                    src="{{ $donorLogoUrl }}"
                    alt="{{ $donorName }}"
                    onerror="this.style.display='none'; this.previousElementSibling.classList.remove('logo-avatar--backup');"
                  >
                @endif
              </div>
            @empty
              @foreach ($defaultDonorLogos as $defaultLogo)
                @php
                  $defaultInitial = strtoupper(substr($defaultLogo['name'], 0, 1)) ?: 'P';
                @endphp
                <div class="logo-item">
                  <span class="logo-avatar logo-avatar--backup">{{ $defaultInitial }}</span>
                  <img
                    src="{{ $defaultLogo['src'] }}"
                    alt="{{ $defaultLogo['name'] }}"
                    onerror="this.style.display='none'; this.previousElementSibling.classList.remove('logo-avatar--backup');"
                  >
                </div>
              @endforeach
            @endforelse
          </div>
        @endfor

      </div>
    </div>

  
</section>

  <section id="bantuan" class="bantuan-section">
  <div class="bantuan-container">
    <div class="section-header">
      <span>KATEGORI BANTUAN</span>
      <h2>Pilih Bantuan Yang Diperlukan</h2>
      <p>
        Pilih kategori bantuan mengikut keperluan pelajar. Setiap permohonan akan disemak
        oleh pentadbir sebelum diluluskan.
      </p>
    </div>

    <div class="bantuan-grid">
      <div class="bantuan-card">
        <div class="bantuan-img">
          <img src="/image/donations/keperluan/makanan1.jpg" alt="Keperluan Asas">
        </div>
        <div class="bantuan-content">
          <span class="tag">Keperluan Harian</span>
          <h3>Keperluan Asas</h3>
          <p>Bantuan makanan asas seperti beras, minyak, gula, tepung, maggi dan biskut.</p>
          <button type="button" class="start-btn bantuan-home-action" data-home-bantuan-open="asas">
            Lihat Bantuan →
          </button>
        </div>
      </div>

      <div class="bantuan-card">
        <div class="bantuan-img">
          <img src="/image/donations/pembelajaran/stationery.jpg" alt="Pembelajaran">
        </div>
        <div class="bantuan-content">
          <span class="tag">Akademik</span>
          <h3>Pembelajaran</h3>
          <p>Bantuan alat tulis, buku nota, laptop, tablet dan kalkulator saintifik.</p>
          <button type="button" class="start-btn bantuan-home-action" data-home-bantuan-open="pembelajaran">
            Lihat Bantuan →
          </button>
        </div>
      </div>

      <div class="bantuan-card">
        <div class="bantuan-img">
          <img src="/image/donations/sukan/futsal.jpg" alt="Sukan">
        </div>
        <div class="bantuan-content">
          <span class="tag">Aktiviti Sukan</span>
          <h3>Sukan</h3>
          <p>Bantuan raket badminton, bola futsal, bola tampar, shuttlecock dan lain-lain.</p>
          <button type="button" class="start-btn bantuan-home-action" data-home-bantuan-open="sukan">
            Lihat Bantuan →
          </button>
        </div>
      </div>
    </div>
  </div>
</section>

<section id="syarat" class="syarat-section">
  <div class="syarat-container">
    <div class="section-header">
      <span>SYARAT PERMOHONAN</span>
      <h2>Syarat Wajib & Khusus</h2>
      <p>Ringkasan syarat untuk semua permohonan dan syarat khusus mengikut kategori bantuan.</p>
    </div>

    <div class="syarat-grid">
      <div class="syarat-card">
        <div class="card-top">
          <h3>Syarat Wajib</h3>
          <span>Semua kategori</span>
        </div>

        <ul class="wajib-list">
          <li>Pelajar UKM yang aktif dan berdaftar sepenuh masa</li>
          <li>Bantuan untuk kegunaan sendiri sahaja</li>
          <li>Menghadapi kesukaran kewangan</li>
          <li>Maklumat dan dokumen mestilah benar dan lengkap</li>
          <li>Satu permohonan bagi setiap kategori dalam satu semester mengikut ketetapan</li>
          <li>Penyalahgunaan bantuan adalah dilarang</li>
        </ul>

        <a href="/login" class="syarat-link">Log masuk untuk memohon →</a>
      </div>

      <div class="syarat-card">
        <div class="card-top">
          <h3>Syarat Khusus</h3>
          <span>Ikut kategori</span>
        </div>

        <div class="tab-buttons">
          <button class="tab-btn active" data-tab="alat-tulis">
            Alat Tulis
          </button>

          <button class="tab-btn" data-tab="peralatan">
            Peralatan 
          </button>

          <button class="tab-btn" data-tab="asas">
            Keperluan Asas Harian
          </button>

          <button class="tab-btn" data-tab="sukan">
             Sukan
          </button>
        </div>

        {{-- ALAT TULIS PEMBELAJARAN --}}
        <div class="tab-content active" id="alat-tulis">
            <ul>
                <li>Pemohon menghadapi kekurangan alat tulis atau bahan pembelajaran asas</li>

                <li>Bantuan yang dimohon mestilah digunakan untuk tujuan akademik</li>

                <li>Permohonan hendaklah bersesuaian dengan keperluan semasa pemohon</li>
            </ul>
        </div>

        {{-- PERANTI PEMBELAJARAN --}}
        <div class="tab-content" id="peralatan">
            <ul>
                <li>Pemohon tidak mempunyai peranti pembelajaran yang sesuai atau peranti sedia ada tidak dapat digunakan dengan baik</li>

                <li>Pemohon perlu menjelaskan keadaan peranti semasa seperti rosak, tiada atau digunakan secara berkongsi</li>

                <li>Permohonan adalah terhad kepada satu peranti utama sahaja</li>
            </ul>
        </div>

        {{-- KEPERLUAN ASAS HARIAN --}}
        <div class="tab-content" id="asas">
            <ul>
                <li>Pemohon menghadapi kekangan dalam menampung keperluan harian</li>

                <li>Bantuan yang dimohon adalah bagi meringankan beban kewangan semasa pemohon</li>

                <li>Permohonan hendaklah dibuat berdasarkan keperluan semasa pemohon</li>
            </ul>
        </div>

        {{-- PERALATAN SUKAN --}}
        <div class="tab-content" id="sukan">
            <ul>
                <li>Pemohon aktif menyertai aktiviti atau program sukan di peringkat kolej, fakulti atau universiti</li>

                <li>Bantuan yang dimohon mestilah berkaitan dengan jenis sukan yang disertai</li>

                <li>Permohonan hendaklah bersesuaian dengan keperluan aktiviti sukan pemohon</li>
            </ul>
        </div>

        <a href="#bantuan" class="syarat-link">Rujuk kategori bantuan →</a>
      </div>
    </div>
  </div>
</section>

<script>
  const tabButtons = document.querySelectorAll(".tab-btn");
  const tabContents = document.querySelectorAll(".tab-content");

  tabButtons.forEach((button) => {
    button.addEventListener("click", () => {
      tabButtons.forEach((btn) => btn.classList.remove("active"));
      tabContents.forEach((content) => content.classList.remove("active"));

      button.classList.add("active");
      document.getElementById(button.dataset.tab).classList.add("active");
    });
  });
</script>



<section class="final-cta">

  <div class="final-cta-container">

    <div class="cta-left">
      <h2>eBantuanSiswa UKM</h2>
      <p>Membantu pelajar dengan lebih sistematik.</p>
    </div>

    <div class="cta-links">
      <a href="/login">Mohon Bantuan</a>
      <a href="/login">Jadi Penderma</a>
      <a href="#bantuan">Kategori Bantuan</a>
      <a href="#syarat">Syarat Permohonan</a>
    </div>

    <div class="cta-contact">
      <h4>Hubungi Kami</h4>
      <p>Universiti Kebangsaan Malaysia</p>
      <p>eBantuanSiswa@ukm.edu.my</p>
    </div>

  </div>

</section>

<div id="homeBantuanModal" class="home-bantuan-modal" aria-hidden="true">
  @php
    $homeBantuanItems = $homeBantuanItems ?? collect();
    $homeSportsItems = $homeBantuanItems->get(\App\Models\Item::CATEGORY_SUKAN, collect());
    $homeLearningStationeryItems = $homeLearningStationeryItems ?? collect();
    $homeLearningEquipmentItems = $homeLearningEquipmentItems ?? collect();
    $homeBasicFoodItems = [
      'Beras',
      'Minyak masak',
      'Biskut',
      'Bihun',
      'Gula',
      'Uncang teh',
      'Tepung',
      'Maggi',
    ];
  @endphp

  <button
    type="button"
    class="home-bantuan-overlay"
    data-home-bantuan-close
    aria-label="Tutup popup"
  ></button>

  <div
    class="home-bantuan-dialog"
    role="dialog"
    aria-modal="true"
    aria-labelledby="homeBantuanModalTitle"
  >
    <div class="home-bantuan-header">
      <div>
        <h2 id="homeBantuanModalTitle" class="home-bantuan-title"></h2>
        <p id="homeBantuanModalSubtitle" class="home-bantuan-subtitle"></p>
      </div>

      <button
        type="button"
        class="home-bantuan-close"
        data-home-bantuan-close
        aria-label="Tutup popup"
      >
        X
      </button>
    </div>

    <div class="home-bantuan-body">
      <div class="home-bantuan-panel" data-home-bantuan-panel="asas">
        <div class="home-bantuan-grid">
          @foreach($homeBasicFoodItems as $item)
            <div class="home-bantuan-item">{{ $item }}</div>
          @endforeach
        </div>

        <p class="home-bantuan-note">
          Kuantiti item akan disesuaikan mengikut bilangan penerima.
        </p>
      </div>

      <div class="home-bantuan-panel" data-home-bantuan-panel="pembelajaran">
        <div class="home-bantuan-tabs" role="tablist" aria-label="Kategori bantuan pembelajaran">
          <button
            type="button"
            class="home-bantuan-tab active"
            data-home-learning-tab="alat"
            role="tab"
            aria-selected="true"
          >
            Alat Tulis & Bahan Pembelajaran
          </button>

          <button
            type="button"
            class="home-bantuan-tab"
            data-home-learning-tab="peralatan"
            role="tab"
            aria-selected="false"
          >
            Peralatan Pembelajaran
          </button>
        </div>

        <div class="home-bantuan-tab-panel active" data-home-learning-panel="alat" role="tabpanel">
          <div class="home-bantuan-grid">
            @forelse($homeLearningStationeryItems as $item)
              <div class="home-bantuan-item">{{ $item->nama_item }}</div>
            @empty
              <div class="home-bantuan-item">Tiada item aktif buat masa ini.</div>
            @endforelse
          </div>
        </div>

        <div class="home-bantuan-tab-panel" data-home-learning-panel="peralatan" role="tabpanel">
          <div class="home-bantuan-grid">
            @forelse($homeLearningEquipmentItems as $item)
              <div class="home-bantuan-item">{{ $item->nama_item }}</div>
            @empty
              <div class="home-bantuan-item">Tiada item aktif buat masa ini.</div>
            @endforelse
          </div>
        </div>

        <p class="home-bantuan-note">
          Pelajar boleh memilih jenis bantuan pembelajaran yang diperlukan semasa mengisi borang permohonan.
        </p>
      </div>

      <div class="home-bantuan-panel" data-home-bantuan-panel="sukan">
        <div class="home-bantuan-grid">
          @forelse($homeSportsItems as $item)
            <div class="home-bantuan-item">{{ $item->nama_item }}</div>
          @empty
            <div class="home-bantuan-item">Tiada item aktif buat masa ini.</div>
          @endforelse
        </div>

        <p class="home-bantuan-note">
          Item sukan akan disesuaikan mengikut keperluan pelajar, kelab atau program.
        </p>
      </div>

      <div class="home-bantuan-actions">
        <a href="{{ route('permohonan.index') }}" class="start-btn home-bantuan-apply">
          Mohon Bantuan
        </a>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const modal = document.getElementById('homeBantuanModal');

  if (!modal) {
    return;
  }

  const title = document.getElementById('homeBantuanModalTitle');
  const subtitle = document.getElementById('homeBantuanModalSubtitle');
  const panels = modal.querySelectorAll('[data-home-bantuan-panel]');
  const closeButtons = modal.querySelectorAll('[data-home-bantuan-close]');
  const learningTabs = modal.querySelectorAll('[data-home-learning-tab]');
  const learningPanels = modal.querySelectorAll('[data-home-learning-panel]');
  let lastTrigger = null;

  const copy = {
    asas: {
      title: 'Pakej Keperluan Asas',
      subtitle: 'Senarai item yang akan diterima oleh pelajar.',
    },
    pembelajaran: {
      title: 'Bantuan Pembelajaran',
      subtitle: 'Pilih kategori bantuan pembelajaran yang diperlukan.',
    },
    sukan: {
      title: 'Bantuan Peralatan Sukan',
      subtitle: 'Senarai peralatan sukan yang boleh dimohon.',
    },
  };

  function setLearningTab(selectedTab) {
    const activeTab = selectedTab || 'alat';

    learningTabs.forEach(function (tab) {
      const isActive = tab.dataset.homeLearningTab === activeTab;
      tab.classList.toggle('active', isActive);
      tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
    });

    learningPanels.forEach(function (panel) {
      panel.classList.toggle('active', panel.dataset.homeLearningPanel === activeTab);
    });
  }

  function openModal(type, trigger) {
    if (!copy[type]) {
      return;
    }

    lastTrigger = trigger;
    title.textContent = copy[type].title;
    subtitle.textContent = copy[type].subtitle;

    panels.forEach(function (panel) {
      panel.classList.toggle('active', panel.dataset.homeBantuanPanel === type);
    });

    if (type === 'pembelajaran') {
      setLearningTab('alat');
    }

    modal.classList.add('active');
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
    modal.querySelector('.home-bantuan-close')?.focus();
  }

  function closeModal() {
    modal.classList.remove('active');
    modal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
    lastTrigger?.focus();
  }

  document.querySelectorAll('[data-home-bantuan-open]').forEach(function (trigger) {
    trigger.addEventListener('click', function () {
      openModal(trigger.dataset.homeBantuanOpen, trigger);
    });
  });

  closeButtons.forEach(function (button) {
    button.addEventListener('click', closeModal);
  });

  learningTabs.forEach(function (tab) {
    tab.addEventListener('click', function () {
      setLearningTab(tab.dataset.homeLearningTab);
    });
  });

  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape' && modal.classList.contains('active')) {
      closeModal();
    }
  });
});
</script>

@include('components.chatbot')

</body>

</html>
