// مكوّن أيقونات بسيط وموحّد لكل التطبيق.
// بدل ما نستورد مكتبة أيقونات خارجية (زي lucide-react)، عملنا svg يدوي صغير
// لأنه مشروعك الأول برياكت وهاد أبسط طريقة تفهم فيها "شو معنى مكوّن قابل لإعادة الاستخدام".
// الفكرة: بدل ما نكرر <svg>...</svg> بكل صفحة، منستدعي <Icon name="calendar" /> بس.
function Icon({ name, size = 20, strokeWidth = 1.8, className = '' }) {
  const paths = {
    gauge: (
      <>
        <path d="M4 15a8 8 0 1 1 16 0" />
        <path d="M12 15l3.5-4.2" />
        <path d="M12 15a1.6 1.6 0 1 0 0-3.2 1.6 1.6 0 0 0 0 3.2Z" fill="currentColor" stroke="none" />
        <path d="M6.5 8.5l.9.9M17.5 8.5l-.9.9M4 15h1.4M18.6 15H20M12 6v1.6" />
      </>
    ),
    home: (
      <>
        <path d="M4 11.5 12 4l8 7.5" />
        <path d="M6 10v9a1 1 0 0 0 1 1h4v-5.5h2V20h4a1 1 0 0 0 1-1v-9" />
      </>
    ),
    calendar: (
      <>
        <rect x="4" y="5.5" width="16" height="14.5" rx="2.4" />
        <path d="M4 10h16" />
        <path d="M8 3.5v3.5M16 3.5v3.5" />
      </>
    ),
    book: (
      <>
        <path d="M5 5.2c1.8-.9 4.2-.9 7 0v13.6c-2.8-.9-5.2-.9-7 0Z" />
        <path d="M19 5.2c-1.8-.9-4.2-.9-7 0v13.6c2.8-.9 5.2-.9 7 0Z" />
      </>
    ),
    target: (
      <>
        <circle cx="12" cy="12" r="7.5" />
        <circle cx="12" cy="12" r="4" />
        <circle cx="12" cy="12" r="0.6" fill="currentColor" stroke="none" />
      </>
    ),
    settings: (
      <>
        <circle cx="12" cy="12" r="3.2" />
        <path d="M12 3.5v2.6M12 17.9v2.6M20.5 12h-2.6M6.1 12H3.5M17.7 6.3l-1.8 1.8M8.1 15.9l-1.8 1.8M17.7 17.7l-1.8-1.8M8.1 8.1 6.3 6.3" />
      </>
    ),
    users: (
      <>
        <circle cx="8.5" cy="8.5" r="3" />
        <path d="M2.8 19c.7-3 2.9-4.7 5.7-4.7s5 1.7 5.7 4.7" />
        <circle cx="16.3" cy="8" r="2.4" />
        <path d="M15 14.5c2.3.2 4 1.8 4.6 4.3" />
      </>
    ),
    clock: (
      <>
        <circle cx="12" cy="12" r="8" />
        <path d="M12 7.5V12l3 2" />
      </>
    ),
    wallet: (
      <>
        <rect x="3.5" y="6.5" width="17" height="12" rx="2.2" />
        <path d="M3.5 10.5h17" />
        <circle cx="16.5" cy="14.3" r="1.1" fill="currentColor" stroke="none" />
      </>
    ),
    check: <path d="M4.5 12.5 9.5 17.5 19.5 6.5" />,
    x: (
      <>
        <path d="M6 6l12 12" />
        <path d="M18 6 6 18" />
      </>
    ),
    lock: (
      <>
        <rect x="5" y="10.5" width="14" height="9" rx="2" />
        <path d="M8 10.5V8a4 4 0 0 1 8 0v2.5" />
      </>
    ),
    bell: (
      <>
        <path d="M6 10.5a6 6 0 0 1 12 0c0 3.4.9 4.8 1.6 5.6H4.4C5.1 15.3 6 13.9 6 10.5Z" />
        <path d="M10.2 19a1.9 1.9 0 0 0 3.6 0" />
      </>
    ),
    logout: (
      <>
        <path d="M14.5 4.5H7A1.5 1.5 0 0 0 5.5 6v12A1.5 1.5 0 0 0 7 19.5h7.5" />
        <path d="M10.5 12H20M20 12l-3-3M20 12l-3 3" />
      </>
    ),
    chevron: <path d="M14.5 6 8.5 12l6 6" />,
    sparkle: (
      <>
        <path d="M12 3.5c.5 3 2.2 4.7 5.2 5.2-3 .5-4.7 2.2-5.2 5.2-.5-3-2.2-4.7-5.2-5.2 3-.5 4.7-2.2 5.2-5.2Z" />
        <path d="M18.5 15.5c.25 1.4 1 2.15 2.4 2.4-1.4.25-2.15 1-2.4 2.4-.25-1.4-1-2.15-2.4-2.4 1.4-.25 2.15-1 2.4-2.4Z" />
      </>
    ),
    steering: (
      <>
        <circle cx="12" cy="12" r="8" />
        <circle cx="12" cy="12" r="2.1" />
        <path d="M12 5.6v4.3M7.2 15.4l3.3-2.2M16.8 15.4l-3.3-2.2" />
      </>
    ),
  };

  return (
    <svg
      className={`icon ${className}`}
      width={size}
      height={size}
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      strokeWidth={strokeWidth}
      strokeLinecap="round"
      strokeLinejoin="round"
      aria-hidden="true"
    >
      {paths[name] ?? null}
    </svg>
  );
}

export default Icon;
