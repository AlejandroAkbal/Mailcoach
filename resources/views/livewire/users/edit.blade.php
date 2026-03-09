<form
    class="card-grid"
    wire:submit="save"
    @keydown.prevent.window.cmd.s="$wire.save()"
    @keydown.prevent.window.ctrl.s="$wire.save()"
    method="POST"
>
    @csrf
    @method('PUT')

    <x-mailcoach::card>
        <x-mailcoach::text-field type="email" :label="__mc('Email')" name="email" wire:model="email" required />

        <x-mailcoach::text-field :label="__mc('Name')" name="name" wire:model="name" required />

        <x-mailcoach::form-buttons>
            <x-mailcoach::button :label="__mc('Save user')" />
            @if($user->welcome_valid_until)
                <button type="button" wire:click="resendInvitation" class="button-secondary">
                    {{ __mc('Resend invitation mail') }}
                </button>
            @endif
        </x-mailcoach::form-buttons>
    </x-mailcoach::card>
</form>
