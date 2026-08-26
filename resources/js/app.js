import './bootstrap';

// Livewire v3 bundles and starts its own Alpine instance (via @livewireScripts,
// with the Livewire directive plugin already registered on it). Starting a
// second, separate Alpine here silently breaks every wire:* directive in the
// app, since wire:click/wire:model are implemented as an Alpine plugin.
