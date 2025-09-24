@props([])

<div class="border-t border-gray-200 dark:border-gray-700 px-4 py-3 sm:px-6">
    <x-molecules.pagination 
        :currentPage="1"
        :totalPages="10"
        :total="100"
        :perPage="10"
        baseUrl=""
    />
</div>