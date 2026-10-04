<?php $rede = $rede ?? ''; ?>
<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
<?php if ($rede === 'instagram'): ?>
    <rect x="3.5" y="3.5" width="17" height="17" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.2" cy="6.8" r="0.6" fill="currentColor"/>
<?php elseif ($rede === 'tiktok'): ?>
    <path d="M14 3.5v11.2a3.8 3.8 0 1 1-3.8-3.8"/><path d="M14 3.5c.4 2.6 2.2 4.4 5 4.6"/>
<?php elseif ($rede === 'youtube'): ?>
    <rect x="2.5" y="5.5" width="19" height="13" rx="4"/><path d="M10.2 9.2v5.6l4.8-2.8z" fill="currentColor"/>
<?php elseif ($rede === 'facebook'): ?>
    <path d="M14.5 20.5v-7h2.6l.4-3h-3V8.7c0-.9.3-1.5 1.6-1.5h1.5V4.6c-.3 0-1.2-.1-2.2-.1-2.2 0-3.7 1.3-3.7 3.8v2.2H9.2v3h2.5v7"/>
<?php endif; ?>
</svg>
