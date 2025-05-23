<x-filament-panels::page>
    <h1 class="text-2xl font-bold mb-4">Assignments for {{ \App\Models\Course::where('course_code', $this->course_code)->first()->course_name ?? 'Unknown Course' }}</h1>
    {{ $this->table }}
</x-filament-panels::page>
