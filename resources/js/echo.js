import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

const isSecure = window.location.protocol === 'https:';
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