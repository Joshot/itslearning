@extends('layouts.app')

@section('title', 'Edit Profile')

@section('content')

<style>
    .tab-button {
        flex: 1;
        text-align: center;
        padding: 10px;
        cursor: pointer;
        font-weight: bold;
        border-bottom: 2px solid transparent;
        transition: all 0.3s ease;
    }
    .tab-button.active {
        border-bottom: 2px solid #106587;
        color: #106587;
    }
    .disabled-input {
        background-color: #e5e7eb;
        cursor: not-allowed;
    }
    .password-container {
        position: relative;
    }
    .password-toggle {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        color: #6b7280;
        transition: color 0.2s ease;
    }
    .password-toggle:hover {
        color: #106587;
    }
    .dashboard-container {
        display: flex;
        gap: 2rem;
        width: 100%;
        max-width: 80%;
        margin: 0 auto;
        padding: 1rem 0;
        justify-content: center;
        align-items: flex-start;
        box-sizing: border-box;
    }
    .sidebar {
        flex: 2;
        padding: 2rem;
        min-width: 0;
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    .main-content {
        flex: 8;
        padding: 2rem;
        min-width: 0;
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    .profile-card img {
        object-fit: cover;
        border-radius: 9999px;
        border: 2px solid #e2e8f0;
        transition: border-color 0.3s ease;
    }
    .profile-card img:hover {
        border-color: #106587;
    }
    .action-btn {
        background: #106587;
        padding: 0.75rem 1.5rem;
        border-radius: 0.5rem;
        font-size: 1rem;
        font-weight: 500;
        transition: background 0.3s ease, transform 0.2s ease;
        width: 100%;
        text-align: center;
    }
    .action-btn:hover {
        background: #0d4a6b;
        transform: translateY(-2px);
    }
    .delete-btn {
        background: #dc2626;
        padding: 0.75rem 1.5rem;
        border-radius: 0.5rem;
        font-size: 1rem;
        font-weight: 500;
        transition: background 0.3s ease, transform 0.2s ease;
        width: 100%;
        text-align: center;
    }
    .delete-btn:hover {
        background: #b91c1c;
        transform: translateY(-2px);
    }
    .save-btn {
        background: #16a34a;
        padding: 0.75rem 1.5rem;
        border-radius: 0.5rem;
        font-size: 1rem;
        font-weight: 500;
        transition: background 0.3s ease, transform 0.2s ease;
        width: 100%;
        text-align: center;
    }
    .save-btn:hover {
        background: #15803d;
        transform: translateY(-2px);
    }
    .back-btn {
        background: #6b7280;
        padding: 0.75rem 1.5rem;
        border-radius: 0.5rem;
        font-size: 1rem;
        font-weight: 500;
        transition: background 0.3s ease, transform 0.2s ease;
        width: 100%;
        text-align: center;
    }
    .back-btn:hover {
        background: #4b5563;
        transform: translateY(-2px);
    }
    input, textarea {
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        padding: 0.75rem;
        width: 100%;
        transition: border-color 0.3s ease, box-shadow 0.3s ease;
    }
    input:focus, textarea:focus {
        border-color: #106587;
        box-shadow: 0 0 0 3px rgba(16, 101, 135, 0.1);
        outline: none;
    }
    label {
        font-size: 0.875rem;
        font-weight: 500;
        color: #374151;
        margin-bottom: 0.5rem;
        display: block;
    }

    /* SweetAlert Mobile Styling */
    .swal2-popup {
        font-size: 1rem !important;
        padding: 1rem !important;
    }
    .swal2-title {
        font-size: 1.25rem !important;
    }
    .swal2-content {
        font-size: 0.875rem !important;
    }
    .swal2-confirm, .swal2-cancel {
        font-size: 0.875rem !important;
        padding: 0.5rem 1rem !important;
    }

    /* Responsive Styles */
    @media (max-width: 1400px) {
        .sidebar, .main-content {
            flex: 1;
        }
    }

    @media (max-width: 1100px) {
        .dashboard-container {
            flex-direction: column;
            gap: 1.5rem;
            max-width: 100%;
            padding: 1rem;
            align-items: center;
            width: 100vw;
            overflow-x: hidden;
        }
        .sidebar, .main-content {
            flex: none;
            width: 100%;
            max-width: 600px;
            height: auto;
            min-height: 400px;
            padding: 1.5rem;
            margin: 0 auto;
            box-sizing: border-box;
        }
        .profile-card img.w-56.h-56 {
            width: 12rem;
            height: 12rem;
        }
        .text-2xl {
            font-size: 1.75rem;
        }
        .action-btn, .delete-btn, .save-btn, .back-btn {
            padding: 0.6rem 1.2rem;
            font-size: 0.95rem;
        }
        input, textarea {
            padding: 0.6rem;
            font-size: 0.95rem;
        }
        label {
            font-size: 0.85rem;
        }
    }

    @media (max-width: 768px) {
        .dashboard-container {
            padding: 0.5rem;
            width: 100vw;
            max-width: 100%;
            margin: 0;
        }
        .profile-card img.w-56.h-56 {
            width: 10rem;
            height: 10rem;
        }
        .text-2xl {
            font-size: 1.5rem;
        }
        .action-btn, .delete-btn, .save-btn, .back-btn {
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
        }
        input, textarea {
            padding: 0.5rem;
            font-size: 0.9rem;
        }
        label {
            font-size: 0.8rem;
        }
        .password-toggle svg {
            width: 1.25rem;
            height: 1.25rem;
        }
        .swal2-popup {
            width: 90% !important;
            max-width: 300px !important;
            font-size: 0.9rem !important;
            padding: 0.75rem !important;
        }
        .swal2-title {
            font-size: 1.1rem !important;
        }
        .swal2-content {
            font-size: 0.8rem !important;
        }
        .swal2-confirm, .swal2-cancel {
            font-size: 0.8rem !important;
            padding: 0.4rem 0.8rem !important;
        }
    }

    @media (max-width: 480px) {
        .dashboard-container {
            max-width: 100%;
            width: 100vw;
            gap: 1rem;
            padding: 0.5rem;
            margin: 0;
            overflow-x: hidden;
        }
        .sidebar, .main-content {
            padding: 1rem;
            min-height: 350px;
            margin: 0;
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
        }
        .profile-card img.w-56.h-56 {
            width: 8rem;
            height: 8rem;
        }
        .text-2xl {
            font-size: 1.25rem;
        }
        .action-btn, .delete-btn, .save-btn, .back-btn {
            padding: 0.5rem 0.8rem;
            font-size: 0.85rem;
        }
        input, textarea {
            padding: 0.5rem;
            font-size: 0.85rem;
        }
        label {
            font-size: 0.75rem;
        }
        .password-toggle svg {
            width: 1rem;
            height: 1rem;
        }
        .swal2-popup {
            width: 85% !important;
            max-width: 280px !important;
            font-size: 0.85rem !important;
            padding: 0.5rem !important;
        }
        .swal2-title {
            font-size: 1rem !important;
        }
        .swal2-content {
            font-size: 0.75rem !important;
        }
        .swal2-confirm, .swal2-cancel {
            font-size: 0.75rem !important;
            padding: 0.3rem 0.7rem !important;
        }
    }

    /* Ensure no horizontal overflow */
    html, body {
        margin: 0;
        padding: 0;
        width: 100%;
        overflow-x: hidden;
    }
</style>

<div class="flex justify-center items-center min-h-[80vh]">
    <div class="flex w-full max-w-[100%] dashboard-container">
        <!-- Sidebar (2 bagian) -->
        <div class="flex-[2] bg-white shadow-lg rounded-2xl p-8 h-[600px] overflow-y-auto flex flex-col profile-card sidebar">
            <h2 class="text-2xl font-semibold">Profile Photo</h2>

            @php
            $user = Auth::guard('lecturer')->check() ? Auth::guard('lecturer')->user() : Auth::guard('student')->user();
            $photo = $user->profile_photo ?? '/images/profile.jpg';
            $photoPath = Str::startsWith($photo, '/images/') ? $photo : asset('storage/' . $photo);
            @endphp

            <img src="{{ $photoPath }}"
                 alt="Profile Picture" class="w-56 h-56 rounded-full mx-auto mt-4" id="profilePhoto">

            <div class="mt-4 flex flex-col items-center">
                @csrf
                <input type="file" id="profile_photo" name="profile_photo" accept="image/jpeg,image/png,image/jpg" class="hidden" onchange="previewImage(event)">
                <button type="button" class="action-btn text-white mb-2" onclick="document.getElementById('profile_photo').click()">Update Photo</button>
                <button type="button" class="delete-btn text-white mb-2" onclick="deletePhoto()">Delete Photo</button>
            </div>

            <!-- Tombol Save dan Back dipaksa ke bawah -->
            <div class="mt-auto flex flex-col items-center">
                <button type="button" class="save-btn text-white mb-2" onclick="saveProfile()">Save</button>
                <button type="button" class="back-btn text-white" onclick="confirmBack()">Back</button>
            </div>
        </div>

        <!-- Konten Utama (8 bagian) -->
        <div class="flex-[8] bg-white shadow-lg rounded-2xl p-8 h-[600px] overflow-y-auto flex flex-col main-content">
            <h2 class="text-2xl font-semibold mb-6">Edit Profile</h2>
            <form id="profileForm" action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PATCH')
                <input type="file" id="profile_photo_hidden" name="image" class="hidden">

                <!-- Lecturer Mode -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label for="name" class="block">Name</label>
                        <input type="text" id="name" name="name" value="{{ Auth::guard('lecturer')->user()->name }}" class="disabled-input" disabled>
                    </div>
                    <div class="mb-4">
                        <label for="nidn" class="block">NIDN</label>
                        <input type="text" id="nidn" name="nidn" value="{{ Auth::guard('lecturer')->user()->nidn }}" class="disabled-input" disabled>
                    </div>
                    <div class="mb-4">
                        <label for="email" class="block">Email</label>
                        <input type="email" id="email" name="email" value="{{ Auth::guard('lecturer')->user()->email }}" class="disabled-input" disabled>
                    </div>
                    <div class="mb-4">
                        <label for="major" class="block">Major</label>
                        <input type="text" id="major" name="major" value="{{ Auth::guard('lecturer')->user()->major }}" class="disabled-input" disabled>
                    </div>
                    <div class="mb-4">
                        <label for="mata_kuliah" class="block">Mata Kuliah</label>
                        <input type="text" id="mata_kuliah" name="mata_kuliah" value="{{ Auth::guard('lecturer')->user()->mata_kuliah }}" class="disabled-input" disabled>
                    </div>
                    <div class="mb-4">
                        <label for="motto" class="block">Motto</label>
                        <input type="text" id="motto" name="motto" value="{{ Auth::guard('lecturer')->user()->motto ?? '' }}">
                    </div>
                    <div class="mb-4">
                        <label for="password" class="block">Password</label>
                        <div class="password-container">
                            <input type="password" id="password" name="password">
                            <span class="password-toggle" onclick="togglePassword('password', 'eyeIcon')">
                                <svg id="eyeIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </span>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="password_confirmation" class="block">Confirm Password</label>
                        <div class="password-container">
                            <input type="password" id="password_confirmation" name="password_confirmation">
                            <span class="password-toggle" onclick="togglePassword('password_confirmation', 'eyeIconConfirm')">
                                <svg id="eyeIconConfirm" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </span>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- SweetAlert untuk Notifikasi dan Konfirmasi -->
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    let isFormDirty = false;
    let originalFormData = {};
    let originalPhoto = "{{ Auth::guard('lecturer')->check() ? (Auth::guard('lecturer')->user()->profile_photo ? asset('storage/' . Auth::guard('lecturer')->user()->profile_photo) : '/images/profile.jpg') : (Auth::guard('student')->user()->profile_photo ? asset('storage/' . Auth::guard('student')->user()->profile_photo) : '/images/profile.jpg') }}";

    document.addEventListener("DOMContentLoaded", function() {
        const form = document.getElementById("profileForm");
        const photoInput = document.getElementById("profile_photo");
        const updatePhotoButton = document.querySelector(".action-btn");
        const inputs = form.querySelectorAll("input:not([disabled])");

        // Save initial form data
        inputs.forEach(input => {
            originalFormData[input.name] = input.value || '';
            input.addEventListener("input", () => {
                isFormDirty = true;
            });
        });

        // Ensure photo upload works on mobile
        updatePhotoButton.addEventListener("click", () => {
            photoInput.click();
        });

        photoInput.addEventListener("change", () => {
            const file = photoInput.files[0];
            if (file) {
                const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
                if (!allowedTypes.includes(file.type)) {
                    Swal.fire({
                        title: 'Format Tidak Didukung',
                        text: 'Silakan unggah gambar dengan format .jpg, .jpeg, atau .png.',
                        icon: 'warning',
                        confirmButtonColor: '#106587',
                        confirmButtonText: 'OK'
                    });
                    photoInput.value = "";
                    return;
                }

                isFormDirty = true;
                const hiddenPhotoInput = document.getElementById("profile_photo_hidden");
                hiddenPhotoInput.files = photoInput.files;

                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById("profilePhoto").src = e.target.result;

                    Swal.fire({
                        title: 'Foto Berhasil Diunggah!',
                        text: 'Foto profil berhasil dipilih dan ditampilkan.',
                        icon: 'success',
                        confirmButtonColor: '#106587',
                        confirmButtonText: 'OK'
                    });
                };
                reader.readAsDataURL(file);
            } else {
                document.getElementById("profilePhoto").src = originalPhoto;
            }
        });

        window.previewImage = function(event) {
            const input = event.target;
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById("profilePhoto").src = e.target.result;
                };
                reader.readAsDataURL(input.files[0]);
            }
        };

        window.deletePhoto = function() {
            isFormDirty = true;
            document.getElementById("profilePhoto").src = "/images/profile.jpg";
            document.getElementById("profile_photo").value = "";
            document.getElementById("profile_photo_hidden").value = "";

            const existingDeleteInput = document.querySelector("input[name='delete_photo']");
            if (existingDeleteInput) {
                existingDeleteInput.remove();
            }
            document.getElementById("profileForm").insertAdjacentHTML("beforeend", '<input type="hidden" name="delete_photo" value="1">');

            Swal.fire({
                title: 'Foto Dihapus!',
                text: 'Foto profil telah dihapus.',
                icon: 'success',
                confirmButtonColor: '#106587',
                confirmButtonText: 'OK'
            });
        };

        window.saveProfile = function() {
            if (!isFormDirty) {
                Swal.fire({
                    title: 'Informasi',
                    text: 'Tidak ada perubahan untuk disimpan.',
                    icon: 'info',
                    confirmButtonColor: '#106587',
                    confirmButtonText: 'OK'
                });
                return;
            }
            Swal.fire({
                title: 'Konfirmasi',
                text: 'Apakah Anda yakin ingin menyimpan perubahan?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#106587',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yakin',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById("profileForm").submit();
                }
            });
        };

        window.confirmBack = function() {
            const redirectUrl = "{{ Auth::guard('lecturer')->check() ? route('lecturer.dashboard') : route('dashboard') }}";
            if (isFormDirty) {
                Swal.fire({
                    title: 'Konfirmasi',
                    text: 'Apakah Anda yakin ingin keluar? Anda belum menyimpan perubahan.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#106587',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Keluar',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = redirectUrl;
                    } else if (result.dismiss === Swal.DismissReason.cancel) {
                        Swal.fire({
                            title: 'Simpan Perubahan',
                            text: 'Apakah Anda ingin menyimpan sebelum keluar?',
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonColor: '#106587',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Simpan',
                            cancelButtonText: 'Batal'
                        }).then((saveResult) => {
                            if (saveResult.isConfirmed) {
                                document.getElementById("profileForm").submit();
                            }
                        });
                    }
                });
            } else {
                window.location.href = redirectUrl;
            }
        };

        window.togglePassword = function(inputId, iconId) {
            const input = document.getElementById(inputId);
            const eyeIcon = document.getElementById(iconId);
            const parentSpan = eyeIcon.parentElement;

            if (input.type === "password") {
                input.type = "text";
                eyeIcon.outerHTML = `<svg id="${iconId}" class="w-5 h-5" fill="none" stroke="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.885 9.885l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                </svg>`;
            } else {
                input.type = "password";
                eyeIcon.outerHTML = `<svg id="${iconId}" class="w-5 h-5" fill="none" stroke="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>`;
            }
            parentSpan.setAttribute('onclick', `togglePassword('${inputId}', '${iconId}')`);
        };

    @if (session('success'))
            Swal.fire({
                title: 'Berhasil!',
                text: '{{ session('success') }}',
                icon: 'success',
                confirmButtonColor: '#106587',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "{{ Auth::guard('lecturer')->check() ? route('lecturer.dashboard') : route('dashboard') }}";
                }
            });
    @endif

    @if (session('error'))
            Swal.fire({
                title: 'Error!',
                text: '{{ session('error') }}',
                icon: 'error',
                confirmButtonColor: '#106587',
                confirmButtonText: 'OK'
            });
    @endif
    });
</script>
@endpush
@endsection
