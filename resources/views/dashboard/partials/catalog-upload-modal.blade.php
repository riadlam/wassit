{{-- Expects $itemLabel: "emote" | "recall". Uses parent Alpine catalogItemPicker scope. --}}
<div
    x-show="uploadOpen"
    x-cloak
    class="fixed inset-0 z-[80] flex items-center justify-center p-4"
    @keydown.escape.window="closeUploadModal()"
>
    <div class="absolute inset-0 bg-black/70" @click="closeUploadModal()"></div>
    <div
        class="relative w-full max-w-md rounded-xl p-5 shadow-2xl"
        style="background-color: #1b1a1e; border: 1px solid #2d2c31;"
        @click.stop
    >
        <div class="mb-4 flex items-start justify-between gap-3">
            <div>
                <h3 class="text-lg font-semibold text-white">Upload {{ $itemLabel }}</h3>
                <p class="mt-1 text-xs text-gray-400">Saved to the catalog so you can reuse it on any listing.</p>
            </div>
            <button type="button" @click="closeUploadModal()" class="text-gray-400 hover:text-white">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="space-y-4">
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-300">Name</label>
                <input
                    type="text"
                    x-model="uploadName"
                    maxlength="120"
                    placeholder="e.g. New {{ ucfirst($itemLabel) }}"
                    class="wizard-input"
                    @keydown.enter.prevent="submitUpload()"
                >
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-300">Image</label>
                <input
                    type="file"
                    accept="image/jpeg,image/png,image/jpg,image/webp,image/gif"
                    class="block w-full text-sm text-gray-300 file:mr-3 file:rounded-md file:border-0 file:bg-red-600 file:px-3 file:py-2 file:text-xs file:font-medium file:text-white hover:file:bg-red-700"
                    @change="onUploadFileChange($event)"
                >
                <p class="mt-1 text-[11px] text-gray-500">PNG, JPG, WEBP, or GIF up to 5MB.</p>
                <div x-show="uploadPreviewUrl" x-cloak class="mt-3 flex justify-center rounded-lg bg-black/30 p-3 ring-1 ring-[#2d2c31]">
                    <img :src="uploadPreviewUrl" alt="Preview" class="max-h-32 max-w-full object-contain">
                </div>
            </div>
            <p x-show="uploadError" x-cloak class="text-sm text-red-400" x-text="uploadError"></p>
        </div>

        <div class="mt-5 flex justify-end gap-2">
            <button
                type="button"
                @click="closeUploadModal()"
                class="rounded-md px-4 py-2 text-sm text-gray-300 ring-1 ring-[#2d2c31] hover:text-white"
            >Cancel</button>
            <button
                type="button"
                @click="submitUpload()"
                :disabled="uploadSaving"
                class="inline-flex items-center gap-2 rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 disabled:opacity-50"
            >
                <i x-show="uploadSaving" class="fa-solid fa-spinner fa-spin"></i>
                <span x-text="uploadSaving ? 'Saving…' : 'Save & select'"></span>
            </button>
        </div>
    </div>
</div>
