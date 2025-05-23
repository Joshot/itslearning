<?php

namespace App\Filament\Pages;

use App\Models\CourseAssignment;
use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;

class CourseAssignments extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Assignments';
    protected static string $view = 'filament.pages.course-assignments';
    protected static bool $shouldRegisterNavigation = false;

    public $course_code;

    public function mount(string $course_code): void
    {
        $this->course_code = $course_code;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(CourseAssignment::query()->where('course_code', $this->course_code))
            ->columns([
                TextColumn::make('user.name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Assigned At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([])
            ->actions([
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }
}
