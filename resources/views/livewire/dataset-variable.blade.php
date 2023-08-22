<div class="mb-3">
    <form wire:submit>

        <label class="form-label">Variable</label>
        <input
            class="form-control"
            type="text"
            placeholder="variable-name"
            wire:model.blur="name"
            wire:dirty.class="border-yellow"
        >
    </form>
</div>
