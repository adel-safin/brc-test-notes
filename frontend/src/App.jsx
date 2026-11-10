import { useEffect, useState } from 'react'
import './App.css'

async function errorMessage(response) {
  let payload = null

  try {
    payload = await response.json()
  } catch {
    payload = null
  }

  if (response.status === 422 && payload?.errors) {
    return Object.values(payload.errors).flat().join(' ')
  }

  if (response.status === 404) {
    return 'Заметка не найдена.'
  }

  return payload?.message || 'Не удалось выполнить запрос.'
}

async function request(url, options = {}) {
  const response = await fetch(url, {
    ...options,
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      ...options.headers,
    },
  })

  if (!response.ok) {
    throw new Error(await errorMessage(response))
  }

  if (response.status === 204) {
    return null
  }

  return response.json()
}

function preview(body) {
  const text = body.replace(/\s+/g, ' ').trim()

  if (text.length <= 90) {
    return text
  }

  return `${text.slice(0, 90)}...`
}

export default function App() {
  const [notes, setNotes] = useState([])
  const [loading, setLoading] = useState(true)
  const [loadError, setLoadError] = useState('')
  const [title, setTitle] = useState('')
  const [body, setBody] = useState('')
  const [editingId, setEditingId] = useState(null)
  const [formError, setFormError] = useState('')
  const [saving, setSaving] = useState(false)

  useEffect(() => {
    let ignore = false

    request('/api/notes')
      .then((payload) => {
        if (!ignore) {
          setNotes(payload.data)
          setLoadError('')
        }
      })
      .catch((error) => {
        if (!ignore) {
          setLoadError(error.message)
        }
      })
      .finally(() => {
        if (!ignore) {
          setLoading(false)
        }
      })

    return () => {
      ignore = true
    }
  }, [])

  function resetForm() {
    setTitle('')
    setBody('')
    setEditingId(null)
    setFormError('')
  }

  function editNote(note) {
    setEditingId(note.id)
    setTitle(note.title)
    setBody(note.body)
    setFormError('')
  }

  async function onSubmit(event) {
    event.preventDefault()
    setSaving(true)
    setFormError('')

    try {
      const payload = await request(editingId ? `/api/notes/${editingId}` : '/api/notes', {
        method: editingId ? 'PUT' : 'POST',
        body: JSON.stringify({ title, body }),
      })
      const note = payload.data

      setNotes((current) => [note, ...current.filter((item) => item.id !== note.id)])
      resetForm()
    } catch (error) {
      setFormError(error.message)
    } finally {
      setSaving(false)
    }
  }

  async function removeNote(note) {
    setFormError('')

    try {
      await request(`/api/notes/${note.id}`, { method: 'DELETE' })
      setNotes((current) => current.filter((item) => item.id !== note.id))

      if (editingId === note.id) {
        resetForm()
      }
    } catch (error) {
      setFormError(error.message)
    }
  }

  return (
    <main className="page">
      <header className="top">
        <div>
          <h1>Заметки</h1>
          <p>Короткий список. Заголовок и текст</p>
        </div>
        <a href="/docs/api">Документация API</a>
      </header>

      <div className="layout">
        <section className="panel" aria-labelledby="list-heading">
          <h2 id="list-heading">Список</h2>
          {loading && <p className="muted">Загрузка...</p>}
          {loadError && <p className="error">{loadError}</p>}
          {!loading && !loadError && notes.length === 0 && (
            <p className="muted">Пока нет заметок.</p>
          )}
          <ul className="notes">
            {notes.map((note) => (
              <li key={note.id} className={note.id === editingId ? 'is-active' : ''}>
                <button type="button" className="note" onClick={() => editNote(note)}>
                  <strong>{note.title}</strong>
                  <span>{preview(note.body)}</span>
                </button>
                <button type="button" className="danger" onClick={() => removeNote(note)}>
                  Удалить
                </button>
              </li>
            ))}
          </ul>
        </section>

        <section className="panel" aria-labelledby="form-heading">
          <h2 id="form-heading">{editingId ? 'Правка' : 'Новая заметка'}</h2>
          <form onSubmit={onSubmit}>
            <label htmlFor="title">Заголовок</label>
            <input
              id="title"
              name="title"
              value={title}
              maxLength={255}
              onChange={(event) => setTitle(event.target.value)}
            />

            <label htmlFor="body">Текст</label>
            <textarea
              id="body"
              name="body"
              rows={8}
              value={body}
              maxLength={10000}
              onChange={(event) => setBody(event.target.value)}
            />

            {formError && <p className="error">{formError}</p>}

            <div className="actions">
              <button type="submit" className="primary" disabled={saving}>
                {saving ? 'Сохранение...' : editingId ? 'Сохранить' : 'Создать'}
              </button>
              {editingId && (
                <button type="button" onClick={resetForm}>
                  Отмена
                </button>
              )}
            </div>
          </form>
        </section>
      </div>
    </main>
  )
}
