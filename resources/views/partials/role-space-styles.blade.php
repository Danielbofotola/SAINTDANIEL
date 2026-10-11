<style>
        :root {
            --ink: #193c39;
            --ink-deep: #12312f;
            --ink-soft: #315653;
            --paper: #f3f6f2;
            --white: #fff;
            --line: #e2e9e2;
            --muted: #71817d;
            --lime: #c8ef74;
            --lime-deep: #aacd57;
            --coral: #d8735a;
            --blue: #648fc6;
            --shadow: 0 12px 30px rgba(20, 57, 53, .055);
        }
        * { box-sizing: border-box; }
        body { margin: 0; color: #233b38; background: var(--paper); font-family: 'DM Sans', sans-serif; font-size: 14px; }
        a { color: inherit; text-decoration: none; }
        button, a { -webkit-tap-highlight-color: transparent; }
        .teacher-shell { min-height: 100vh; }
        .teacher-sidebar { position: fixed; inset: 0 auto 0 0; z-index: 10; display: flex; width: 254px; flex-direction: column; padding: 27px 18px 20px; color: #eff7ec; background: var(--ink); }
        .brand { display: flex; align-items: center; gap: 11px; padding: 0 10px 31px; }
        .brand-mark { display: grid; width: 38px; height: 38px; place-items: center; color: var(--ink); background: var(--lime); border-radius: 12px 12px 12px 4px; font-family: 'Manrope', sans-serif; font-size: 16px; font-weight: 800; }
        .brand-name { font-family: Arial, sans-serif; font-size: 16px; font-weight: 800; letter-spacing: .3px; }
        .school-brand { font-family: Arial, sans-serif; }
        .brand-caption { display: block; margin-top: 2px; color: #a7bfba; font-size: 10px; font-weight: 600; letter-spacing: 1.1px; text-transform: uppercase; }
        .nav-caption { padding: 0 12px 10px; color: #88a7a1; font-size: 10px; font-weight: 700; letter-spacing: 1.25px; text-transform: uppercase; }
        .teacher-nav { display: grid; gap: 6px; }
        .teacher-nav a { display: flex; min-height: 44px; align-items: center; gap: 13px; padding: 0 13px; color: #c2d4d0; border-radius: 9px; font-size: 13px; font-weight: 600; transition: background .18s ease, color .18s ease; }
        .teacher-nav a i { width: 17px; text-align: center; font-size: 15px; }
        .teacher-nav a:hover, .teacher-nav a.active { color: #f5f9f3; background: #2a514d; }
        .teacher-nav a.active { box-shadow: inset 3px 0 var(--lime); }
        .sidebar-bottom { margin-top: auto; }
        .profile-shortcut { display: flex; align-items: center; gap: 11px; padding: 13px 9px; border-top: 1px solid rgba(218, 239, 231, .15); }
        .avatar { display: grid; width: 38px; height: 38px; flex: 0 0 38px; place-items: center; overflow: hidden; color: var(--ink); background: var(--lime); border-radius: 50%; font-family: 'Manrope', sans-serif; font-size: 14px; font-weight: 800; }
        .avatar img { width: 100%; height: 100%; object-fit: cover; }
        .profile-name { overflow: hidden; color: #f4f8f2; font-size: 12px; font-weight: 700; text-overflow: ellipsis; white-space: nowrap; }
        .profile-role { margin-top: 3px; color: #9cb6b0; font-size: 11px; }
        .sidebar-logout { width: 100%; padding: 10px 12px; color: #bed0cc; background: transparent; border: 0; border-radius: 8px; cursor: pointer; font: inherit; text-align: left; }
        .sidebar-logout i { margin-right: 10px; }
        .sidebar-logout:hover { color: #fff; background: #2a514d; }
        .teacher-main { min-height: 100vh; margin-left: 254px; padding: 0 38px 44px; }
        .topbar { display: flex; min-height: 76px; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--line); }
        .breadcrumb { color: var(--muted); font-size: 12px; }
        .breadcrumb strong { color: var(--ink); font-weight: 700; }
        .topbar-actions { display: flex; align-items: center; gap: 10px; }
        .icon-link { display: grid; width: 38px; height: 38px; place-items: center; color: var(--ink-soft); background: #fff; border: 1px solid var(--line); border-radius: 50%; }
        .icon-link:hover { color: var(--ink); border-color: var(--lime-deep); }
        .top-avatar { display: flex; align-items: center; gap: 9px; margin-left: 6px; }
        .top-avatar .avatar { width: 34px; height: 34px; flex-basis: 34px; font-size: 12px; }
        .top-avatar-name { color: var(--ink); font-size: 12px; font-weight: 700; }
        .top-avatar-role { margin-top: 2px; color: var(--muted); font-size: 10px; }
        .dashboard-content { width: min(100%, 1390px); margin: 0 auto; }
        .page-intro { display: flex; align-items: flex-end; justify-content: space-between; gap: 18px; padding: 30px 0 22px; }
        .eyebrow { margin: 0 0 7px; color: #718981; font-size: 10px; font-weight: 700; letter-spacing: 1.4px; text-transform: uppercase; }
        h1, h2, h3, p { margin-top: 0; }
        h1 { margin-bottom: 5px; color: var(--ink-deep); font-family: 'Manrope', sans-serif; font-size: 28px; font-weight: 800; letter-spacing: 0; line-height: 1.2; }
        .intro-subtitle { margin: 0; color: var(--muted); font-size: 13px; }
        .date-stamp { color: var(--ink-soft); font-size: 12px; font-weight: 700; white-space: nowrap; }
        .welcome-banner { position: relative; display: flex; min-height: 176px; align-items: center; justify-content: space-between; overflow: hidden; padding: 27px 33px; color: #f5f9f3; background: var(--ink); border-radius: 13px; }
        .welcome-banner::before, .welcome-banner::after { position: absolute; content: ''; border: 1px solid rgba(200, 239, 116, .16); border-radius: 50%; pointer-events: none; }
        .welcome-banner::before { top: -138px; right: 75px; width: 330px; height: 330px; }
        .welcome-banner::after { top: -104px; right: 109px; width: 262px; height: 262px; }
        .welcome-copy { position: relative; z-index: 1; max-width: 620px; }
        .welcome-tag { display: inline-flex; align-items: center; gap: 7px; margin-bottom: 13px; padding: 6px 9px; color: var(--lime); background: rgba(200, 239, 116, .1); border: 1px solid rgba(200, 239, 116, .18); border-radius: 5px; font-size: 10px; font-weight: 700; letter-spacing: .7px; text-transform: uppercase; }
        .welcome-tag i { font-size: 9px; }
        .welcome-banner h2 { margin: 0 0 7px; color: #fff; font-family: 'Manrope', sans-serif; font-size: 24px; font-weight: 800; line-height: 1.25; }
        .welcome-banner p { margin: 0; color: #bdd0cb; font-size: 12px; }
        .banner-action { position: relative; z-index: 1; display: inline-flex; min-height: 40px; align-items: center; gap: 10px; padding: 0 15px; color: var(--ink); background: var(--lime); border-radius: 7px; font-size: 12px; font-weight: 700; white-space: nowrap; transition: background .18s ease, transform .18s ease; }
        .banner-action:hover { color: var(--ink); background: #d7f593; transform: translateY(-1px); }
        .banner-art { position: absolute; top: 50%; right: 11%; display: grid; width: 84px; height: 84px; place-items: center; color: var(--ink); background: var(--lime); border-radius: 25px 25px 25px 7px; box-shadow: 18px 16px 0 rgba(255,255,255,.06); font-size: 31px; transform: translateY(-50%) rotate(-7deg); }
        .stat-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 13px; margin: 17px 0 25px; }
        .stat-card { display: flex; min-height: 105px; align-items: center; gap: 13px; padding: 17px; background: var(--white); border: 1px solid var(--line); border-radius: 10px; box-shadow: var(--shadow); }
        .stat-icon { display: grid; width: 42px; height: 42px; flex: 0 0 42px; place-items: center; color: var(--ink); background: #e8f1df; border-radius: 12px; font-size: 16px; }
        .stat-card:nth-child(2) .stat-icon { color: #a45c49; background: #f8e9e4; }
        .stat-card:nth-child(3) .stat-icon { color: #4c75a7; background: #e8eef7; }
        .stat-card:nth-child(4) .stat-icon { color: #92742c; background: #f6f0d9; }
        .stat-value { color: var(--ink-deep); font-family: 'Manrope', sans-serif; font-size: 24px; font-weight: 800; line-height: 1; }
        .stat-label { margin-top: 6px; color: var(--muted); font-size: 11px; font-weight: 600; }
        .dashboard-grid { display: grid; grid-template-columns: minmax(0, 1.45fr) minmax(280px, .85fr); gap: 22px; align-items: start; }
        .content-section { min-width: 0; margin-bottom: 24px; }
        .section-heading { display: flex; align-items: flex-end; justify-content: space-between; gap: 10px; margin-bottom: 12px; }
        .section-heading h2 { margin: 0; color: var(--ink-deep); font-family: 'Manrope', sans-serif; font-size: 16px; font-weight: 800; }
        .section-heading p { margin: 4px 0 0; color: var(--muted); font-size: 11px; }
        .section-link { color: #48736e; font-size: 11px; font-weight: 700; white-space: nowrap; }
        .section-link:hover { color: var(--coral); }
        .schedule-panel, .evaluation-panel { overflow: hidden; background: #fff; border: 1px solid var(--line); border-radius: 10px; box-shadow: var(--shadow); }
        .schedule-day { padding: 12px 16px; color: var(--ink-soft); background: #f8faf7; border-bottom: 1px solid var(--line); font-size: 10px; font-weight: 700; letter-spacing: .9px; text-transform: uppercase; }
        .schedule-item { display: grid; grid-template-columns: 64px 3px minmax(0, 1fr) auto; align-items: center; gap: 13px; min-height: 76px; padding: 12px 16px; border-bottom: 1px solid #edf1ed; }
        .schedule-item:last-child { border-bottom: 0; }
        .schedule-time { color: var(--ink); font-size: 12px; font-weight: 700; white-space: nowrap; }
        .schedule-time small { display: block; margin-top: 3px; color: #91a09b; font-size: 10px; font-weight: 500; }
        .schedule-marker { width: 3px; height: 34px; background: var(--lime-deep); border-radius: 2px; }
        .schedule-item:nth-child(3n) .schedule-marker { background: var(--coral); }
        .schedule-item:nth-child(4n) .schedule-marker { background: var(--blue); }
        .schedule-course { min-width: 0; color: var(--ink-deep); font-size: 12px; font-weight: 700; }
        .schedule-meta { overflow: hidden; margin-top: 4px; color: var(--muted); font-size: 10px; text-overflow: ellipsis; white-space: nowrap; }
        .room-chip { padding: 5px 8px; color: #61746f; background: #f2f5f1; border-radius: 5px; font-size: 10px; white-space: nowrap; }
        .empty-state { display: grid; min-height: 146px; place-items: center; padding: 22px; color: var(--muted); text-align: center; }
        .empty-state i { display: grid; width: 38px; height: 38px; margin: 0 auto 9px; place-items: center; color: #81938c; background: #f0f4ef; border-radius: 50%; font-size: 15px; }
        .empty-state strong { display: block; margin-bottom: 4px; color: var(--ink-soft); font-size: 12px; }
        .empty-state span { display: block; max-width: 300px; font-size: 11px; line-height: 1.5; }
        .course-list { display: grid; gap: 9px; }
        .course-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; min-height: 69px; padding: 12px 14px; background: #fff; border: 1px solid var(--line); border-radius: 9px; }
        .course-row:hover { border-color: #b8caac; }
        .course-title { color: var(--ink-deep); font-size: 12px; font-weight: 700; }
        .course-details { margin-top: 4px; color: var(--muted); font-size: 10px; }
        .course-code { flex: 0 0 auto; padding: 5px 7px; color: #53716c; background: #edf3e9; border-radius: 4px; font-size: 9px; font-weight: 700; letter-spacing: .45px; }
        .evaluation-item { display: flex; align-items: center; gap: 11px; padding: 13px 15px; border-bottom: 1px solid #edf1ed; }
        .evaluation-item:last-child { border-bottom: 0; }
        .evaluation-date { display: grid; width: 43px; height: 46px; flex: 0 0 43px; align-content: center; justify-items: center; color: var(--ink); background: #eef4e9; border-radius: 7px; }
        .evaluation-date strong { font-family: 'Manrope', sans-serif; font-size: 16px; line-height: 1; }
        .evaluation-date span { margin-top: 3px; color: #62776f; font-size: 8px; font-weight: 700; text-transform: uppercase; }
        .evaluation-copy { min-width: 0; }
        .evaluation-title { overflow: hidden; color: var(--ink-deep); font-size: 11px; font-weight: 700; text-overflow: ellipsis; white-space: nowrap; }
        .evaluation-meta { margin-top: 4px; color: var(--muted); font-size: 10px; }
        .link-notice { display: flex; align-items: flex-start; gap: 12px; margin: 17px 0 4px; padding: 15px; color: #6e5b35; background: #fbf5df; border: 1px solid #eee3b8; border-radius: 8px; }
        .link-notice > i { margin-top: 2px; color: #a48231; }
        .link-notice strong { display: block; margin-bottom: 3px; font-size: 11px; }
        .link-notice p { margin: 0; font-size: 10px; line-height: 1.5; }
        .link-notice a { color: #775f26; font-weight: 700; text-decoration: underline; text-underline-offset: 2px; }
        .footer-note { padding-top: 9px; color: #98a5a0; font-size: 10px; }
        @media (max-width: 1100px) {
            .teacher-main { padding-right: 24px; padding-left: 24px; }
            .stat-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .banner-art { right: 7%; }
        }
        @media (max-width: 820px) {
            .teacher-sidebar { width: 76px; align-items: center; padding: 20px 9px; }
            .brand { padding: 0 0 28px; }
            .brand-copy, .nav-caption, .teacher-nav .nav-label, .profile-shortcut > div, .sidebar-logout .logout-label { display: none; }
            .teacher-nav { width: 100%; }
            .teacher-nav a { justify-content: center; padding: 0; }
            .teacher-nav a i { width: auto; font-size: 17px; }
            .teacher-nav a.active { box-shadow: inset 0 -3px var(--lime); }
            .profile-shortcut { justify-content: center; padding: 14px 0; }
            .sidebar-logout { text-align: center; }
            .sidebar-logout i { margin: 0; }
            .teacher-main { margin-left: 76px; }
            .dashboard-grid { grid-template-columns: minmax(0, 1fr); gap: 3px; }
            .right-column { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
        }
        @media (max-width: 560px) {
            .teacher-sidebar { inset: auto 0 0; width: auto; height: 66px; flex-direction: row; justify-content: center; padding: 7px 10px; box-shadow: 0 -5px 18px rgba(21, 56, 52, .1); }
            .brand, .sidebar-bottom { display: none; }
            .teacher-nav { display: flex; width: 100%; max-width: 380px; justify-content: space-around; gap: 4px; }
            .teacher-nav a { display: grid; min-width: 60px; min-height: 50px; align-content: center; justify-items: center; gap: 4px; padding: 4px 7px; font-size: 9px; }
            .teacher-nav .nav-label { display: block; }
            .teacher-nav a.active { box-shadow: inset 0 -2px var(--lime); }
            .teacher-main { margin: 0; padding: 0 16px 88px; }
            .topbar { min-height: 61px; }
            .top-avatar-copy { display: none; }
            .page-intro { align-items: flex-start; padding: 23px 0 17px; }
            h1 { font-size: 23px; }
            .intro-subtitle { max-width: 230px; font-size: 11px; line-height: 1.5; }
            .date-stamp { padding-top: 15px; font-size: 10px; }
            .welcome-banner { min-height: 169px; padding: 23px 20px; }
            .welcome-banner h2 { max-width: 245px; font-size: 20px; }
            .welcome-banner p { max-width: 225px; font-size: 11px; line-height: 1.5; }
            .banner-action { margin-top: 16px; }
            .banner-art { top: 22px; right: 19px; width: 43px; height: 43px; border-radius: 14px 14px 14px 4px; font-size: 17px; transform: rotate(-7deg); }
            .welcome-banner::before { top: -165px; right: -185px; }
            .welcome-banner::after { top: -130px; right: -150px; }
            .stat-grid { gap: 8px; margin: 12px 0 22px; }
            .stat-card { min-height: 83px; gap: 9px; padding: 11px 10px; }
            .stat-icon { width: 34px; height: 34px; flex-basis: 34px; border-radius: 10px; font-size: 13px; }
            .stat-value { font-size: 20px; }
            .stat-label { font-size: 9px; }
            .right-column { grid-template-columns: minmax(0, 1fr); gap: 0; }
            .schedule-item { grid-template-columns: 54px 3px minmax(0, 1fr); gap: 10px; padding: 11px; }
            .room-chip { display: none; }
            .section-heading h2 { font-size: 15px; }
            .section-link { font-size: 10px; }
        }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { scroll-behavior: auto !important; transition-duration: .01ms !important; }
        }
    </style>
