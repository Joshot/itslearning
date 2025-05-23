<?php

namespace App\Filament\Pages;

use App\Models\Question;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Request;

class DifficultyQuestions extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-folder-open';
    protected static string $view = 'filament.pages.difficulty-questions';
    protected static ?string $navigationLabel = 'Soal per Kesulitan';
    public static function shouldRegisterNavigation(): bool
    {
        return false; // Sembunyikan dari sidebar
    }

    public function table(Table $table): Table
    {
        $courseCode = Request::query('course_code');
        return $table
            ->query(Question::whereHas('quiz', fn ($q) => $q->where('course_code', $courseCode)))
            ->columns([
                Tables\Columns\TextColumn::make('quiz.title')
                    ->label('Quiz')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('question_text')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('difficulty')
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'easy' => 'success',
                        'medium' => 'warning',
                        'hard' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('correct_option')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('difficulty')
                    ->options([
                        'easy' => 'Easy',
                        'medium' => 'Medium',
                        'hard' => 'Hard',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->url(fn (Question $record) => route('filament.admin.resources.questions.edit', $record)),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public function getTitle(): string
    {
        $course = \App\Models\Course::where('course_code', Request::query('course_code'))->first();
        return $course ? "Soal untuk {$course->course_name}" : 'Soal per Kesulitan';
    }
}
