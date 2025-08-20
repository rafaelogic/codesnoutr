@props([
    'multiple' => false,
    'accept' => '',
    'maxSize' => '10MB',
    'dropzone' => true,
    'preview' => true,
    'name' => 'files',
    'label' => 'Choose files or drag and drop',
    'hint' => 'PNG, JPG, PDF up to 10MB'
])

@php
    $inputId = uniqid('file_');
@endphp

<div 
    x-data="fileUpload()"
    class="w-full"
    {{ $attributes }}
>
    @if($dropzone)
        <div 
            @dragover.prevent="dragover = true"
            @dragleave.prevent="dragover = false" 
            @drop.prevent="handleDrop($event)"
            :class="{ 'border-blue-500 bg-blue-50': dragover }"
            class="relative border-2 border-gray-300 border-dashed rounded-lg p-6 transition-colors duration-200 hover:border-gray-400"
        >
            <input 
                type="file"
                id="{{ $inputId }}"
                name="{{ $name }}"
                @if($multiple) multiple @endif
                @if($accept) accept="{{ $accept }}" @endif
                @change="handleFiles($event.target.files)"
                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
            />
            
            <div class="text-center">
                <x-atoms.icon name="cloud-upload" size="xl" color="muted" class="mx-auto mb-4" />
                <div class="text-sm">
                    <label for="{{ $inputId }}" class="relative cursor-pointer rounded-md font-medium text-blue-600 hover:text-blue-500">
                        {{ $label }}
                    </label>
                </div>
                @if($hint)
                    <p class="text-xs text-gray-500 mt-1">{{ $hint }}</p>
                @endif
            </div>
        </div>
    @else
        <div class="flex items-center space-x-3">
            <x-atoms.button variant="secondary" type="button" onclick="document.getElementById('{{ $inputId }}').click()">
                <x-atoms.icon name="paperclip" size="sm" class="mr-2" />
                Choose Files
            </x-atoms.button>
            
            <input 
                type="file"
                id="{{ $inputId }}"
                name="{{ $name }}"
                @if($multiple) multiple @endif
                @if($accept) accept="{{ $accept }}" @endif
                @change="handleFiles($event.target.files)"
                class="hidden"
            />
            
            <span x-text="files.length > 0 ? files.length + ' file(s) selected' : 'No files selected'" class="text-sm text-gray-500"></span>
        </div>
    @endif
    
    @if($preview)
        <!-- File Preview -->
        <div x-show="files.length > 0" class="mt-4 space-y-2">
            <template x-for="(file, index) in files" :key="index">
                <div class="flex items-center justify-between p-3 border border-gray-200 rounded-md bg-gray-50">
                    <div class="flex items-center space-x-3">
                        <x-atoms.icon name="document-text" size="sm" color="secondary" />
                        <div class="flex-1 min-w-0">
                            <p x-text="file.name" class="text-sm font-medium text-gray-900 truncate"></p>
                            <p x-text="formatFileSize(file.size)" class="text-xs text-gray-500"></p>
                        </div>
                    </div>
                    <button 
                        type="button"
                        @click="removeFile(index)"
                        class="text-red-400 hover:text-red-600 focus:outline-none"
                    >
                        <x-atoms.icon name="x" size="sm" />
                    </button>
                </div>
            </template>
        </div>
    @endif
</div>

<script>
function fileUpload() {
    return {
        files: [],
        dragover: false,
        
        handleFiles(fileList) {
            this.files = Array.from(fileList);
        },
        
        handleDrop(e) {
            this.dragover = false;
            const files = e.dataTransfer.files;
            this.handleFiles(files);
        },
        
        removeFile(index) {
            this.files.splice(index, 1);
        },
        
        formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
    }
}
</script>
