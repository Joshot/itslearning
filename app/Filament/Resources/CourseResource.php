<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourseResource\Pages;
use App\Models\Course;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationLabel = 'Kursus';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('course_code')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('course_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('course_milik')
                    ->options([
                        'Informatika' => 'Informatika',
                        'Pertanian' => 'Pertanian',
                        'Sistem Informasi' => 'Sistem Informasi',
                        'Teknik Komputer' => 'Teknik Komputer',
                        'Biologi' => 'Biologi',
                        'Kedokteran' => 'Kedokteran',
                        'Ilmu Komunikasi' => 'Ilmu Komunikasi',
                        'Manajemen' => 'Manajemen',
                        'Film' => 'Film',
                        'DKV' => 'DKV',
                    ])
                    ->required(),
                Forms\Components\Section::make('Course Materials')
                    ->schema([
                        Forms\Components\Repeater::make('materials')
                            ->relationship('materials')
                            ->schema([
                                Forms\Components\TextInput::make('week')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1)
                                    ->maxValue(14),
                                Forms\Components\FileUpload::make('files')
                                    ->label('Files')
                                    ->directory('materials')
                                    ->multiple()
                                    ->acceptedFileTypes([
                                        'application/pdf',
                                        'application/msword',
                                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                        'application/zip',
                                        'image/*',
                                        'audio/*',
                                        'video/*',
                                    ])
                                    ->nullable(),
                                Forms\Components\TextInput::make('video_url')
                                    ->url()
                                    ->nullable(),
                                Forms\Components\Toggle::make('is_optional')
                                    ->label('Optional')
                                    ->default(false),
                            ])
                            ->columns(2),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(function () {
                $query = Course::query();
                if (!Auth::user()->is_admin) {
                    $query->whereHas('assignments', fn ($q) => $q->where('user_id', Auth::id()));
                }
                return $query;
            })
            ->columns([
                Tables\Columns\TextColumn::make('course_code')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('course_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('course_milik')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('materials_count')
                    ->label('Materials')
                    ->counts('materials')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
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
            'index' => Pages\ListCourses::route('/'),
            'create' => Pages\CreateCourse::route('/create'),
            'edit' => Pages\EditCourse::route('/{record}/edit'),
        ];
    }
}
