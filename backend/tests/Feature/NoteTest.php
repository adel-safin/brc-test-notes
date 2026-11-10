<?php

namespace Tests\Feature;

use App\Models\Note;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_notes_newest_first(): void
    {
        $older = Note::factory()->create(['title' => 'Старая']);
        $this->travel(1)->minutes();
        $newer = Note::factory()->create(['title' => 'Новая']);

        $response = $this->getJson('/api/notes');

        $response->assertOk()
            ->assertJsonPath('data.0.id', $newer->id)
            ->assertJsonPath('data.1.id', $older->id)
            ->assertJsonPath('data.0.title', 'Новая');
    }

    public function test_store_creates_a_note(): void
    {
        $response = $this->postJson('/api/notes', [
            'title' => 'Заголовок',
            'body' => 'Текст заметки',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.title', 'Заголовок')
            ->assertJsonPath('data.body', 'Текст заметки');

        $this->assertDatabaseHas('notes', [
            'title' => 'Заголовок',
            'body' => 'Текст заметки',
        ]);
    }

    public function test_store_rejects_missing_and_too_long_fields(): void
    {
        $this->postJson('/api/notes', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'body']);

        $this->postJson('/api/notes', [
            'title' => str_repeat('а', 256),
            'body' => 'Текст',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['title']);

        $this->postJson('/api/notes', [
            'title' => 'Заголовок',
            'body' => '',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['body']);
    }

    public function test_show_returns_one_note(): void
    {
        $note = Note::factory()->create([
            'title' => 'Одна',
            'body' => 'Содержимое',
        ]);

        $this->getJson('/api/notes/'.$note->id)
            ->assertOk()
            ->assertJsonPath('data.id', $note->id)
            ->assertJsonPath('data.body', 'Содержимое');
    }

    public function test_show_returns_not_found_for_missing_or_invalid_id(): void
    {
        $this->getJson('/api/notes/999')->assertNotFound();
        $this->getJson('/api/notes/not-a-number')->assertNotFound();
    }

    public function test_update_replaces_title_and_body(): void
    {
        $note = Note::factory()->create();

        $this->putJson('/api/notes/'.$note->id, [
            'title' => 'Новый заголовок',
            'body' => 'Новый текст',
        ])->assertOk()
            ->assertJsonPath('data.title', 'Новый заголовок')
            ->assertJsonPath('data.body', 'Новый текст');

        $this->assertDatabaseHas('notes', [
            'id' => $note->id,
            'title' => 'Новый заголовок',
            'body' => 'Новый текст',
        ]);
    }

    public function test_update_rejects_empty_body(): void
    {
        $note = Note::factory()->create();

        $this->putJson('/api/notes/'.$note->id, [
            'title' => 'Заголовок',
            'body' => '',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['body']);
    }

    public function test_destroy_deletes_a_note(): void
    {
        $note = Note::factory()->create();

        $this->deleteJson('/api/notes/'.$note->id)->assertNoContent();

        $this->assertDatabaseMissing('notes', ['id' => $note->id]);
    }

    public function test_destroy_returns_not_found_for_missing_note(): void
    {
        $this->deleteJson('/api/notes/999')->assertNotFound();
    }
}
