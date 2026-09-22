<?php

namespace Tests\Feature;

use App\Models\SidebarMenu;
use App\Models\Tank;
use App\Models\TankTelemetry;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MultiRoleSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        // Create secondary test users for role authorization tests
        User::firstOrCreate(
            ['username' => 'Renaldi'],
            [
                'name' => 'Renaldi',
                'email' => 'renaldi@pelindo.co.id',
                'password' => 'Pelindo3',
                'role' => User::ROLE_STAFF,
            ]
        );
        User::firstOrCreate(
            ['username' => 'Operator'],
            [
                'name' => 'Operator BBM',
                'email' => 'operator@pelindo.co.id',
                'password' => 'Pelindo3',
                'role' => User::ROLE_OPERATOR,
            ]
        );
        User::firstOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Administrator',
                'email' => 'admin@pelindo.co.id',
                'password' => 'Pelindo3',
                'role' => User::ROLE_ADMIN,
            ]
        );
    }

    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/login');
    }

    public function test_login_page_renders_successfully(): void
    {
        $response = $this->get('/login');
        $response->assertOk();
        $response->assertSee('Monitoring Tangki BBM Genset');
        $response->assertDontSee('Pilihan Akun Demo');
    }

    public function test_staff_can_login_with_username(): void
    {
        $response = $this->post('/login', [
            'username' => 'Renaldi',
            'password' => 'Pelindo3',
        ]);

        $response->assertRedirect('/monitoring');
        $this->assertAuthenticated();
        $this->assertTrue(auth()->user()->isStaff());
    }

    public function test_operator_can_login_with_username(): void
    {
        $response = $this->post('/login', [
            'username' => 'Operator',
            'password' => 'Pelindo3',
        ]);

        $response->assertRedirect('/monitoring');
        $this->assertAuthenticated();
        $this->assertTrue(auth()->user()->isOperator());
    }

    public function test_admin_can_login_with_username(): void
    {
        $response = $this->post('/login', [
            'username' => 'admin',
            'password' => 'Pelindo3',
        ]);

        $response->assertRedirect('/monitoring');
        $this->assertAuthenticated();
        $this->assertTrue(auth()->user()->isAdmin());
    }

    public function test_superadmin_can_login_with_username(): void
    {
        $response = $this->post('/login', [
            'username' => 'Daniel',
            'password' => 'Pelindo3',
        ]);

        $response->assertRedirect('/monitoring');
        $this->assertAuthenticated();
        $this->assertTrue(auth()->user()->isSuperAdmin());
    }

    public function test_operator_can_record_pemasukan_bbm_with_automatic_liter_calculation(): void
    {
        $operator = User::where('username', 'Operator')->first();
        $tank = Tank::where('code', 'TNK-A')->first(); // capacity=100L, height=70cm

        $response = $this->actingAs($operator)->post('/monitoring/'.$tank->id.'/record-bbm', [
            'type' => 'pemasukan',
            'height_cm' => 35.0, // 35 / 70 = 50% => 50 Liters
            'notes' => 'Penerimaan BBM Truk #01',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tank_telemetries', [
            'tank_id' => $tank->id,
            'source' => 'pemasukan_bbm',
            'height_cm' => 35.0,
            'volume_liters' => 50.0,
            'percentage' => 50.0,
        ]);
    }

    public function test_operator_can_record_pemakaian_bbm_with_automatic_subtraction(): void
    {
        $operator = User::where('username', 'Operator')->first();
        $tank = Tank::where('code', 'TNK-A')->first(); // capacity=100L, height=70cm

        // Set initial fill first to 70cm (100L)
        $this->actingAs($operator)->post('/monitoring/'.$tank->id.'/record-bbm', [
            'type' => 'pemasukan',
            'height_cm' => 70.0,
            'notes' => 'Isi Penuh',
        ]);

        // Record pemakaian, remaining height 42cm (60L => used 40L)
        $response = $this->actingAs($operator)->post('/monitoring/'.$tank->id.'/record-bbm', [
            'type' => 'pemakaian',
            'height_cm' => 42.0,
            'notes' => 'Pemakaian Genset Dermaga',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tank_telemetries', [
            'tank_id' => $tank->id,
            'source' => 'pemakaian_bbm',
            'height_cm' => 42.0,
            'volume_liters' => 60.0,
        ]);
    }

    public function test_cannot_record_height_exceeding_tank_max_height(): void
    {
        $operator = User::where('username', 'Operator')->first();
        $tank = Tank::where('code', 'TNK-A')->first(); // max height = 70cm

        $response = $this->actingAs($operator)->post('/monitoring/'.$tank->id.'/record-bbm', [
            'type' => 'pemasukan',
            'height_cm' => 150.0, // Exceeds 70cm
        ]);

        $response->assertSessionHasErrors('height_cm');
    }

    public function test_staff_can_view_monitoring_grid_and_detail_3d(): void
    {
        $staff = User::where('username', 'Renaldi')->first();
        $tank = Tank::where('code', 'TNK-A')->first();

        $response = $this->actingAs($staff)->get('/monitoring');
        $response->assertOk();
        $response->assertSee('Tangki A');
        $response->assertSee('TNK-A');

        $detailResponse = $this->actingAs($staff)->get('/monitoring/'.$tank->id);
        $detailResponse->assertOk();
        $detailResponse->assertSee($tank->name);
        $detailResponse->assertSee('vendor/three/three.module.js');
    }

    public function test_staff_cannot_access_admin_or_superadmin_pages(): void
    {
        $staff = User::where('username', 'Renaldi')->first();

        $responseAdmin = $this->actingAs($staff)->get('/admin/tanks');
        $responseAdmin->assertForbidden();

        $responseSuper = $this->actingAs($staff)->get('/superadmin/users');
        $responseSuper->assertForbidden();
    }

    public function test_admin_can_create_and_manage_tanks(): void
    {
        $admin = User::where('username', 'admin')->first();

        // 1. Create with manual/auto capacity
        $response = $this->actingAs($admin)->post('/admin/tanks', [
            'name' => 'Tangki Uji Lapangan',
            'code' => 'TNK-TEST',
            'length_cm' => 200.0,
            'width_cm' => 90.0,
            'height_cm' => 70.0,
            'diameter_cm' => 90.0,
            'description' => 'Tangki pengujian baru',
            'is_active' => true,
        ]);

        $response->assertRedirect('/admin/tanks');
        $this->assertDatabaseHas('tanks', [
            'code' => 'TNK-TEST',
            'length_cm' => 200.0,
            'width_cm' => 90.0,
            'height_cm' => 70.0,
            'diameter_cm' => 90.0,
        ]);

        $tank = Tank::where('code', 'TNK-TEST')->first();
        $this->assertNotNull($tank);
        $this->assertGreaterThan(0, $tank->capacity_liters);
    }

    public function test_superadmin_can_toggle_sidebar_menu_visibility(): void
    {
        $superadmin = User::where('username', 'Daniel')->first();
        $menu = SidebarMenu::first();

        $initialState = $menu->is_active;

        $response = $this->actingAs($superadmin)->post('/superadmin/menus/'.$menu->id.'/toggle');
        $response->assertRedirect();

        $this->assertDatabaseHas('sidebar_menus', [
            'id' => $menu->id,
            'is_active' => ! $initialState,
        ]);
    }

    public function test_superadmin_can_create_new_user_with_role(): void
    {
        $superadmin = User::where('username', 'Daniel')->first();

        $response = $this->actingAs($superadmin)->post('/superadmin/users', [
            'name' => 'Operator Dermaga',
            'username' => 'operator01',
            'email' => 'operator01@pelindo.co.id',
            'password' => 'Pelindo3',
            'role' => 'staff',
        ]);

        $response->assertRedirect('/superadmin/users');
        $this->assertDatabaseHas('users', [
            'username' => 'operator01',
            'role' => 'staff',
        ]);
    }

    public function test_operator_can_record_bbm_with_photo_proof_upload(): void
    {
        Storage::fake('public');

        $operator = User::where('username', 'Operator')->first();
        $tank = Tank::where('code', 'TNK-A')->first();

        $photo = UploadedFile::fake()->image('bukti_pemasukan.jpg', 640, 480);

        $response = $this->actingAs($operator)->post('/monitoring/'.$tank->id.'/record-bbm', [
            'type' => 'pemasukan',
            'height_cm' => 50.0,
            'notes' => 'Pemasukan dengan bukti foto',
            'photo' => $photo,
        ]);

        $response->assertRedirect();

        $telemetry = TankTelemetry::where('tank_id', $tank->id)
            ->where('source', 'pemasukan_bbm')
            ->latest('id')
            ->first();

        $this->assertNotNull($telemetry);
        $this->assertNotNull($telemetry->photo_path);
        Storage::disk('public')->assertExists($telemetry->photo_path);
    }

    public function test_can_filter_report_by_jenis_transaksi(): void
    {
        $staff = User::where('username', 'Renaldi')->first();
        $tank = Tank::where('code', 'TNK-A')->first();

        // Create pemasukan and pemakaian logs
        TankTelemetry::create([
            'tank_id' => $tank->id,
            'volume_liters' => 50,
            'percentage' => 50,
            'height_cm' => 35,
            'status' => 'normal',
            'source' => 'pemasukan_bbm',
            'notes' => 'Catatan Pemasukan Khusus',
        ]);

        TankTelemetry::create([
            'tank_id' => $tank->id,
            'volume_liters' => 30,
            'percentage' => 30,
            'height_cm' => 21,
            'status' => 'low',
            'source' => 'pemakaian_bbm',
            'notes' => 'Catatan Pemakaian Khusus',
        ]);

        // Filter pemasukan
        $responsePemasukan = $this->actingAs($staff)->get('/laporan?jenis=pemasukan');
        $responsePemasukan->assertOk();
        $responsePemasukan->assertSee('PEMASUKAN');
        $responsePemasukan->assertSee('Catatan Pemasukan Khusus');

        // Filter pemakaian
        $responsePemakaian = $this->actingAs($staff)->get('/laporan?jenis=pemakaian');
        $responsePemakaian->assertOk();
        $responsePemakaian->assertSee('PEMAKAIAN');
        $responsePemakaian->assertSee('Catatan Pemakaian Khusus');
    }

    public function test_only_admin_and_superadmin_can_export_report_to_excel(): void
    {
        $staff = User::where('username', 'Renaldi')->first();
        $admin = User::where('username', 'admin')->first();
        $superadmin = User::where('username', 'Daniel')->first();

        // Staff is forbidden from exporting
        $this->actingAs($staff)->get('/laporan/export')->assertForbidden();

        // Staff cannot see export button on laporan view
        $this->actingAs($staff)->get('/laporan')->assertOk()->assertDontSee('Ekspor ke Excel');

        // Admin can export and sees export button
        $responseAdmin = $this->actingAs($admin)->get('/laporan/export');
        $responseAdmin->assertOk();
        $this->assertTrue(str_contains($responseAdmin->headers->get('content-type'), 'spreadsheetml'));
        $this->actingAs($admin)->get('/laporan')->assertOk()->assertSee('Ekspor ke Excel');

        // SuperAdmin can export
        $responseSuper = $this->actingAs($superadmin)->get('/laporan/export');
        $responseSuper->assertOk();
        $this->assertTrue(str_contains($responseSuper->headers->get('content-type'), 'spreadsheetml'));
    }

    public function test_operator_can_record_pemasukan_with_photo_file(): void
    {
        Storage::fake('public');
        $operator = User::where('username', 'Operator')->first();
        $tank = Tank::where('code', 'TNK-A')->first();

        $file = UploadedFile::fake()->image('bukti_solar.jpg');

        $response = $this->actingAs($operator)->post('/monitoring/'.$tank->id.'/record-bbm', [
            'type' => 'pemasukan',
            'height_cm' => 45.0,
            'notes' => 'Pemasukan dengan bukti foto',
            'photo' => $file,
        ]);

        $response->assertRedirect();
        $telemetry = TankTelemetry::where('tank_id', $tank->id)->latest('id')->first();
        $this->assertNotNull($telemetry->photo_path);
        Storage::disk('public')->assertExists($telemetry->photo_path);
    }

    public function test_operator_can_record_pemasukan_with_base64_compressed_photo(): void
    {
        Storage::fake('public');
        $operator = User::where('username', 'Operator')->first();
        $tank = Tank::where('code', 'TNK-A')->first();

        // 1x1 transparent PNG/JPG base64
        $fakeBase64 = 'data:image/jpeg;base64,'.base64_encode('fake-image-binary-data');

        $response = $this->actingAs($operator)->post('/monitoring/'.$tank->id.'/record-bbm', [
            'type' => 'pemasukan',
            'height_cm' => 50.0,
            'notes' => 'Pemasukan dengan bukti foto base64',
            'photo_base64' => $fakeBase64,
        ]);

        $response->assertRedirect();
        $telemetry = TankTelemetry::where('tank_id', $tank->id)->latest('id')->first();
        $this->assertNotNull($telemetry->photo_path);
        Storage::disk('public')->assertExists($telemetry->photo_path);
    }
}
