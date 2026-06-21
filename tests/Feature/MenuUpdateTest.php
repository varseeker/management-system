<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\RawMaterial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MenuUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_menu_without_new_image(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $material = RawMaterial::query()->create([
            'name' => 'Telur',
            'unit' => 'butir',
            'stock' => 100,
            'min_stock' => 10,
        ]);

        $menu = Menu::query()->create([
            'code' => 'IND-TELUR',
            'name' => 'Indomie Telur',
            'description' => 'Test',
            'price' => 18000,
            'category' => 'Makanan',
            'most_ordered' => false,
            'is_active' => true,
        ]);

        $menu->rawMaterials()->sync([
            $material->id => ['quantity' => 1],
        ]);

        $response = $this->actingAs($admin)->put(route('menus.update', $menu), [
            'code' => 'IND-TELUR',
            'name' => 'Indomie Telur Spesial',
            'description' => 'Updated',
            'price' => '20000',
            'category' => 'Makanan',
            'is_active' => '1',
            'most_ordered' => '1',
            'ingredients' => [
                ['raw_material_id' => $material->id, 'quantity' => 2],
            ],
        ]);

        $response->assertRedirect(route('menus.index'));
        $this->assertDatabaseHas('menus', [
            'id' => $menu->id,
            'name' => 'Indomie Telur Spesial',
            'price' => 20000,
        ]);
    }

    public function test_admin_can_update_menu_with_image_upload(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => 'admin']);
        $material = RawMaterial::query()->create([
            'name' => 'Kopi',
            'unit' => 'gram',
            'stock' => 100,
            'min_stock' => 10,
        ]);

        $menu = Menu::query()->create([
            'code' => 'KOPI-SUSU',
            'name' => 'Kopi Susu',
            'price' => 15000,
            'category' => 'Minuman',
            'most_ordered' => false,
            'is_active' => true,
        ]);

        $menu->rawMaterials()->sync([
            $material->id => ['quantity' => 1],
        ]);

        $response = $this->actingAs($admin)->put(route('menus.update', $menu), [
            'code' => 'KOPI-SUSU',
            'name' => 'Kopi Susu',
            'price' => '15000',
            'category' => 'Minuman',
            'is_active' => '1',
            'image' => UploadedFile::fake()->image('kopi.jpg'),
            'ingredients' => [
                ['raw_material_id' => $material->id, 'quantity' => 1],
            ],
        ]);

        $response->assertRedirect(route('menus.index'));

        $menu->refresh();
        $this->assertNotNull($menu->image_path);
        Storage::disk('public')->assertExists($menu->image_path);
    }
}
