<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Crea (o repara el rol de) el usuario admin inicial.
 *
 * Nunca hay una contraseña literal en este archivo. El bootstrap real
 * ocurre así:
 *
 *   ADMIN_SEED_EMAIL    (opcional, default go@0km.app)
 *   ADMIN_SEED_PASSWORD (obligatoria para fijar una contraseña conocida)
 *
 * Antes de correr este seeder en un entorno nuevo, setear esas variables
 * en el .env de ESE entorno (nunca commitearlas). Si ADMIN_SEED_PASSWORD
 * no está seteada, el seeder igual crea el usuario pero con una
 * contraseña aleatoria de un solo uso que este código deliberadamente
 * NUNCA imprime, loguea ni guarda en ningún archivo — si no se seteó
 * ADMIN_SEED_PASSWORD de antemano, esa contraseña queda perdida a
 * propósito, y hay que resetearla a mano (php artisan tinker) antes de
 * poder loguearse.
 *
 * Este seeder nunca toca la contraseña de un admin que ya existe — solo
 * crea uno nuevo si `ADMIN_SEED_EMAIL` (o el email default) no existe
 * todavía. La rotación de la contraseña en producción es una acción
 * manual separada, no algo que este archivo haga.
 */
class CreateAdminSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::firstOrCreate(['name' => 'admin'], ['label' => 'Administrador']);

        $email = env('ADMIN_SEED_EMAIL', 'go@0km.app');
        $user = User::where('email', $email)->first();

        if ($user) {
            if (! $user->roles()->where('roles.id', $role->id)->exists()) {
                $user->roles()->attach($role);
            }

            $this->command?->info("Admin ya existe ({$email}) — sin cambios de contraseña.");

            return;
        }

        $password = env('ADMIN_SEED_PASSWORD');
        $wasGenerated = false;

        if (! $password) {
            $password = Str::password(24);
            $wasGenerated = true;
        }

        $user = User::create([
            'name' => 'Nahuel',
            'email' => $email,
            'password' => bcrypt($password),
        ]);

        $user->roles()->attach($role);

        if ($wasGenerated) {
            $this->command?->warn(
                "Admin creado ({$email}) con una contraseña aleatoria que este comando " .
                "NO imprime ni guarda en ningún lado. Si no seteaste ADMIN_SEED_PASSWORD " .
                "antes de correr el seeder, esa contraseña quedó perdida a propósito — " .
                "reseteala ahora con 'php artisan tinker' antes de intentar loguearte."
            );

            return;
        }

        $this->command?->info("Admin creado: {$email} (contraseña desde ADMIN_SEED_PASSWORD).");
    }
}
