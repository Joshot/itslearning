<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuestionResource\Pages;
use App\Models\Question;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class QuestionResource extends Resource
{
    protected static ?string $model = Question::class;
    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';
    protected static ?string $navigationLabel = 'Soal';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('quiz_id')
                    ->relationship('quiz', 'title', fn ($query) => $query->whereHas('course'))
                    ->searchable()
                    ->required()
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->title . ' (' . ($record->course?->course_name ?? 'No Course') . ')')
                    ->default(null)
                    ->reactive(),
                Forms\Components\Textarea::make('question_text')
                    ->required()
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('option_a')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('option_b')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('option_c')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('option_d')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('correct_option')
                    ->options([
                        'A' => 'A',
                        'B' => 'B',
                        'C' => 'C',
                        'D' => 'D',
                    ])
                    ->required(),
                Forms\Components\Select::make('difficulty')
                    ->options([
                        'easy' => 'Easy',
                        'medium' => 'Medium',
                        'hard' => 'Hard',
                    ])
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('quiz.title')
                    ->label('Quiz')
                    ->searchable()
                    ->sortable()
                    ->default('-'),
                Tables\Columns\TextColumn::make('quiz.course.course_name')
                    ->label('Course')
                    ->searchable()
                    ->sortable()
                    ->default('-'),
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
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('difficulty')
                    ->options([
                        'easy' => 'Easy',
                        'medium' => 'Medium',
                        'hard' => 'Hard',
                    ]),
                Tables\Filters\SelectFilter::make('quiz_id')
                    ->relationship('quiz', 'title', fn ($query) => $query->whereHas('course'))
                    ->label('Quiz'),
                Tables\Filters\SelectFilter::make('course_code')
                    ->relationship('quiz.course', 'course_name')
                    ->label('Course'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuestions::route('/'),
            'create' => Pages\CreateQuestion::route('/create'),
            'edit' => Pages\EditQuestion::route('/{record}/edit'),
        ];
    }
}
