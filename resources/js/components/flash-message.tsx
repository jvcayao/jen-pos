import { useEffect, useState } from 'react';

export default function FlashMessage() {
  const [message, setMessage] = useState<string | null>(null);
  const [type, setType] = useState<'success' | 'error' | 'info' | null>(null);

  useEffect(() => {
    // Listen for Inertia flash messages
    const handler = (event: any) => {
      if (event.detail?.page?.props?.flash) {
        setMessage(event.detail.page.props.flash.message);
        setType(event.detail.page.props.flash.type || 'info');
        setTimeout(() => setMessage(null), 4000);
      }
    };
    window.addEventListener('inertia:navigate', handler);
    return () => window.removeEventListener('inertia:navigate', handler);
  }, []);

  if (!message) return null;

  return (
    <div
      className={`fixed top-6 left-1/2 z-50 -translate-x-1/2 px-4 py-2 rounded shadow-lg text-white transition-all
        ${type === 'success' ? 'bg-green-600' : type === 'error' ? 'bg-red-600' : 'bg-blue-600'}`}
      role="alert"
    >
      {message}
    </div>
  );
}
