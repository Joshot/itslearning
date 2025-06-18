<?php

namespace App\Filament\Pages;

use App\Models\Course;
use App\Models\CourseAssignment;
use App\Models\Lecturer;
use App\Models\Student;
use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteBulkAction;

class ManageCourseAssignments extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationLabel = 'Kelola Mata Kuliah';
    protected static string $view = 'filament.pages.manage-course-assignments';

    public function table(Table $table): Table
    {
        return $table
            ->query(Course::query())
            ->columns([
                TextColumn::make('course_name')
                    ->label('Course')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('course_code')
                    ->label('Course Code')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('course_code')
                    ->options(Course::pluck('course_name', 'course_code')->toArray())
                    ->label('Course'),
            ])
            ->actions([
                Action::make('view_assignments')
                    ->label('View Assignments')
                    ->url(fn (Course $record) => route('filament.admin.pages.course-assignments', ['course_code' => $record->course_code])),
                Action::make('edit')
                    ->label('Assign Users')
                    ->form([
                        Forms\Components\Select::make('lecturer_ids')
                            ->label('Lecturers')
                            ->options(Lecturer::pluck('name', 'id')->toArray())
                            ->searchable()
                            ->multiple(),
                        Forms\Components\Select::make('student_ids')
                            ->label('Students')
                            ->options(Student::pluck('name', 'id')->toArray())
                            ->searchable()
                            ->multiple(),
                    ])
                    ->action(function (Course $record, array $data) {
                        // Delete existing assignments for this course
                        CourseAssignment::where('course_code', $record->course_code)->delete();

                        // Create new assignments for lecturers
                        if (!empty($data['lecturer_ids'])) {
                            foreach ($data['lecturer_ids'] as $lecturerId) {
                                CourseAssignment::create([
                                    'course_code' => $record->course_code,
                                    'lecturer_id' => $lecturerId,
                                    'student_id' => null,
                                ]);
                            }
                        }

                        // Create new assignments for students
                        if (!empty($data['student_ids'])) {
                            foreach ($data['student_ids'] as $studentId) {
                                CourseAssignment::create([
                                    'course_code' => $record->course_code,
                                    'lecturer_id' => null,
                                    'student_id' => $studentId,
                                ]);
                            }
                        }
                    }),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->form([
                        Forms\Components\Select::make('course_code')
                            ->options(Course::pluck('course_name', 'course_code')->toArray())
                            ->required(),
                        Forms\Components\Select::make('lecturer_ids')
                            ->label('Lecturers')
                            ->options(Lecturer::pluck('name', 'id')->toArray())
                            ->searchable()
                            ->multiple(),
                        Forms\Components\Select::make('student_ids')
                            ->label('Students')
                            ->options(Student::pluck('name', 'id')->toArray())
                            ->searchable()
                            ->multiple(),
                    ])
                    ->action(function (array $data) {
                        // Create assignments for lecturers
                        if (!empty($data['lecturer_ids'])) {
                            foreach ($data['lecturer_ids'] as $lecturerId) {
                                CourseAssignment::create([
                                    'course_code' => $data['course_code'],
                                    'lecturer_id' => $lecturerId,
                                    'student_id' => null,
                                ]);
                            }
                        }

                        // Create assignments for students
                        if (!empty($data['student_ids'])) {
                            foreach ($data['student_ids'] as $studentId) {
                                CourseAssignment::create([
                                    'course_code' => $data['course_code'],
                                    'lecturer_id' => null,
                                    'student_id' => $studentId,
                                ]);
                            }
                        }
                    }),
            ]);
    }
}
