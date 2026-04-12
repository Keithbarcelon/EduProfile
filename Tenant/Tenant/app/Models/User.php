<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\BelongsToSchool;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, BelongsToSchool;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'school_id',
        'department_id',
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'school_id' => 'integer',
            'department_id' => 'integer',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the department assigned to the user.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the student profile associated with the user.
     */
    public function student(): HasOne
    {
        return $this->hasOne(Student::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_role')
            ->withPivot('assigned_by')
            ->withTimestamps();
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'user_permission')
            ->withPivot('assigned_by')
            ->withTimestamps();
    }

    public function hasPermission(string $permissionSlug): bool
    {
        $slug = trim($permissionSlug);

        if ($slug === '') {
            return false;
        }

        $directPermissions = [];

        if ($this->directPermissionTablesAvailable()) {
            try {
                $directPermissions = $this->permissions()
                    ->pluck('slug')
                    ->unique()
                    ->all();
            } catch (QueryException) {
                $directPermissions = [];
            }
        }

        if (in_array($slug, $directPermissions, true)) {
            return true;
        }

        $rolePermissions = [];

        if ($this->rbacTablesAvailable()) {
            try {
                $rolePermissions = $this->roles()
                    ->with('permissions:id,slug')
                    ->get()
                    ->pluck('permissions')
                    ->flatten()
                    ->pluck('slug')
                    ->unique()
                    ->all();
            } catch (QueryException) {
                // Fall back to legacy static role-to-permission map for partially migrated tenants.
                $rolePermissions = [];
            }
        }

        if (in_array($slug, $rolePermissions, true)) {
            return true;
        }

        $legacyMap = [
            'admin' => ['manage_students', 'view_reports', 'manage_users', 'manage_departments', 'manage_settings', 'manage_status_updates', 'review_documents', 'manage_profiles', 'manage_tenant', 'manage_roles', 'manage_support'],
            'tenant_admin' => ['manage_students', 'view_reports', 'manage_users', 'manage_departments', 'manage_settings', 'manage_status_updates', 'review_documents', 'manage_profiles', 'manage_tenant', 'manage_roles', 'manage_support'],
            'admission' => ['manage_students', 'view_reports', 'manage_status_updates', 'review_documents'],
            'department' => ['manage_students', 'view_reports', 'manage_status_updates', 'review_documents'],
            'faculty' => ['manage_students', 'manage_status_updates', 'review_documents'],
            'student' => [],
        ];

        return in_array($slug, $legacyMap[$this->role] ?? [], true);
    }

    /**
     * @param list<string> $permissionSlugs
     */
    public function hasAnyPermission(array $permissionSlugs): bool
    {
        foreach ($permissionSlugs as $permissionSlug) {
            if ($this->hasPermission($permissionSlug)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Resolve the owned student profile, including legacy records linked only by email.
     */
    public function resolveStudentProfile(): ?Student
    {
        $student = $this->student()->first();

        if ($student) {
            return $student;
        }

        $student = Student::query()
            ->where('school_id', $this->school_id)
            ->where('email', $this->email)
            ->first();

        if (! $student) {
            return null;
        }

        if (! $student->user_id) {
            $student->forceFill(['user_id' => $this->id])->save();
            $student->setRelation('user', $this);
        }

        return $student;
    }

    private function rbacTablesAvailable(): bool
    {
        return Schema::hasTable('roles')
            && Schema::hasTable('permissions')
            && Schema::hasTable('role_permission')
            && Schema::hasTable('user_role');
    }

    private function directPermissionTablesAvailable(): bool
    {
        return Schema::hasTable('permissions')
            && Schema::hasTable('user_permission');
    }
}
