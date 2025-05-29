<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuizResource\Pages;
use App\Models\Quiz;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class QuizResource extends Resource
{
    protected static ?string $model = Quiz::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Kuis';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('course_code')
                    ->relationship('course', 'course_name')
                    ->searchable()
                    ->required()
                    ->label('Course'),
                Forms\Components\TextInput::make('task_number')
                    ->numeric()
                    ->required()
                    ->minValue(1)
                    ->maxValue(4)
                    ->label('Task Number'),
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->label('Title'),
                Forms\Components\DateTimePicker::make('start_time')
                    ->required()
                    ->label('Start Time'),
                Forms\Components\DateTimePicker::make('end_time')
                    ->required()
                    ->after('start_time')
                    ->label('End Time'),
                Forms\Components\Section::make('Questions')
                    ->schema([
                        Forms\Components\Repeater::make('questions')
                            ->relationship('questions')
                            ->schema([
                                Forms\Components\Textarea::make('question_text')
                                    ->required()
                                    ->maxLength(65535)
                                    ->label('Question Text'),
                                Forms\Components\TextInput::make('option_a')
                                    ->required()
                                    ->maxLength(255)
                                    ->label('Option A'),
                                Forms\Components\TextInput::make('option_b')
                                    ->required()
                                    ->maxLength(255)
                                    ->label('Option B'),
                                Forms\Components\TextInput::make('option_c')
                                    ->required()
                                    ->maxLength(255)
                                    ->label('Option C'),
                                Forms\Components\TextInput::make('option_d')
                                    ->required()
                                    ->maxLength(255)
                                    ->label('Option D'),
                                Forms\Components\Select::make('correct_option')
                                    ->options([
                                        'A' => 'A',
                                        'B' => 'B',
                                        'C' => 'C',
                                        'D' => 'D',
                                    ])
                                    ->required()
                                    ->label('Correct Option'),
                                Forms\Components\Select::make('difficulty')
                                    ->options([
                                        'easy' => 'Easy',
                                        'medium' => 'Medium',
                                        'hard' => 'Hard',
                                    ])
                                    ->required()
                                    ->label('Difficulty'),
                            ])
                            ->columns(2),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('course.course_name')
                    ->label('Course')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('task_number')
                    ->label('Task Number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('questions_count')
                    ->label('Questions')
                    ->counts('questions')
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_time')
                    ->dateTime()
                    ->label('Start Time')
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_time')
                    ->dateTime()
                    ->label('End Time')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('course_code')
                    ->relationship('course', 'course_name')
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
            'index' => Pages\ListQuizzes::route('/'),
            'create' => Pages\CreateQuiz::route('/create'),
            'edit' => Pages\EditQuiz::route('/{record}/edit'),
        ];
    }
}
