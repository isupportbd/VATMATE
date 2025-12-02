import Echo from 'laravel-echo';

const isSecure = window.location.protocol === 'https:';

// If you proxy Reverb through 443 when HTTPS is enabled, you might do:
const port = isSecure ? 443 : 6001;

window.Echo = new Echo({
  broadcaster: 'reverb',
  key: import.meta.env.VITE_REVERB_APP_KEY,

  wsHost: window.location.hostname,
  wsPort: port,
  wssPort: 443,

  forceTLS: isSecure,
  encrypted: isSecure,

  // Let both be possible; browser will choose
  enabledTransports: isSecure ? ['wss', 'ws'] : ['ws'],
});
