// ─── Types ────────────────────────────────────────────────────────────────────

export interface User {
  id: number
  email: string
  firstName: string
  lastName: string
  phone: string | null
  roles: string[]
  consentDate: string
  consentVersion: string
  isAnonymized: boolean
  createdAt: string
}

export interface Event {
  id: number
  title: string
  description: string
  eventDate: string
  location: string
  maxParticipants: number
  organizer: { id: number; firstName: string; lastName: string }
  isPublished: boolean
  createdAt: string
  registrationsCount: number
}

export interface Registration {
  id: number
  userId: number
  eventId: number
  registeredAt: string
  status: 'pending' | 'confirmed' | 'cancelled'
  event?: Event
}

// ─── Simulated delay ──────────────────────────────────────────────────────────

const delay = (ms = 350) => new Promise(resolve => setTimeout(resolve, ms))

// ─── Mock database ────────────────────────────────────────────────────────────

let nextEventId = 5
let nextRegistrationId = 3

const mockUsers: (User & { password: string })[] = [
  {
    id: 1,
    email: 'user@test.com',
    password: 'user123',
    firstName: 'Jean',
    lastName: 'Martin',
    phone: '+33 6 12 34 56 78',
    roles: ['ROLE_USER'],
    consentDate: '2026-01-15T10:00:00',
    consentVersion: '1.0',
    isAnonymized: false,
    createdAt: '2026-01-15T10:00:00',
  },
  {
    id: 2,
    email: 'orga@test.com',
    password: 'orga123',
    firstName: 'Marie',
    lastName: 'Dupont',
    phone: null,
    roles: ['ROLE_ORGANIZER'],
    consentDate: '2026-01-20T14:30:00',
    consentVersion: '1.0',
    isAnonymized: false,
    createdAt: '2026-01-20T14:30:00',
  },
]

let mockEvents: Event[] = [
  {
    id: 1,
    title: 'Vue.js Summit 2026',
    description:
      "La plus grande conférence Vue.js de France ! Deux jours intensifs avec des speakers internationaux, des ateliers pratiques sur Vue 3, Pinia, Nuxt et l'écosystème Vite. Rejoignez plus de 150 développeurs passionnés pour explorer les dernières innovations du framework.",
    eventDate: '2026-04-15T09:00:00',
    location: 'Paris – La Défense, CNIT',
    maxParticipants: 150,
    organizer: { id: 2, firstName: 'Marie', lastName: 'Dupont' },
    isPublished: true,
    createdAt: '2026-02-10T09:00:00',
    registrationsCount: 87,
  },
  {
    id: 2,
    title: 'Symfony World — Architecture & APIs',
    description:
      'Conférence dédiée aux architectures modernes avec Symfony 6+. Au programme : API Platform, microservices, DDD, CQRS, Event Sourcing et tests automatisés. Idéal pour les développeurs souhaitant aller plus loin dans leur maîtrise du framework.',
    eventDate: '2026-05-20T14:00:00',
    location: 'Lyon – Centre de Congrès',
    maxParticipants: 200,
    organizer: { id: 2, firstName: 'Marie', lastName: 'Dupont' },
    isPublished: true,
    createdAt: '2026-02-20T11:00:00',
    registrationsCount: 134,
  },
  {
    id: 3,
    title: 'Node.js & DevOps Meetup',
    description:
      "Soirée meetup dédiée aux architectures Node.js modernes et aux pratiques DevOps. On parlera de CI/CD avec GitHub Actions, containers Docker, monitoring avec Grafana, et déploiement sur cloud. Un apéro networking clôturera la soirée !",
    eventDate: '2026-06-10T18:30:00',
    location: 'Bordeaux – Station Numérique',
    maxParticipants: 80,
    organizer: { id: 2, firstName: 'Marie', lastName: 'Dupont' },
    isPublished: true,
    createdAt: '2026-03-01T10:00:00',
    registrationsCount: 42,
  },
  {
    id: 4,
    title: 'Formation RGPD pour Développeurs',
    description:
      "Formation intensive d'une journée sur la mise en conformité RGPD pour les équipes de développement. Gestion du consentement, droits des utilisateurs, Privacy by Design, registre des traitements et audit de conformité. Attestation de formation fournie.",
    eventDate: '2026-07-03T09:00:00',
    location: 'En ligne – Microsoft Teams',
    maxParticipants: 30,
    organizer: { id: 2, firstName: 'Marie', lastName: 'Dupont' },
    isPublished: true,
    createdAt: '2026-03-10T08:00:00',
    registrationsCount: 18,
  },
]

let mockRegistrations: Registration[] = [
  { id: 1, userId: 1, eventId: 1, registeredAt: '2026-03-01T10:00:00', status: 'confirmed' },
  { id: 2, userId: 1, eventId: 3, registeredAt: '2026-03-05T14:00:00', status: 'confirmed' },
]

// Parse userId directly from token — résistant aux HMR reloads
// Format : "mock-jwt-{userId}-{timestamp}"
function getUserFromToken(token: string): (User & { password: string }) | null {
  const match = token.match(/^mock-jwt-(\d+)-\d+$/)
  if (!match) return null
  const userId = parseInt(match[1])
  return mockUsers.find(u => u.id === userId) ?? null
}

function stripPassword(u: User & { password: string }): User {
  const { password: _p, ...rest } = u
  return rest
}

// ─── Auth ─────────────────────────────────────────────────────────────────────

export async function mockLogin(credentials: { email: string; password: string }) {
  await delay()
  const u = mockUsers.find(u => u.email === credentials.email && u.password === credentials.password)
  if (!u) throw { response: { status: 401, data: { message: 'Email ou mot de passe incorrect.' } } }
  const token = `mock-jwt-${u.id}-${Date.now()}`
  return { token, user: stripPassword(u) }
}

export async function mockRegister(data: {
  email: string
  password: string
  firstName: string
  lastName: string
  phone?: string
  consent: boolean
}) {
  await delay()
  if (!data.consent) throw { response: { status: 422, data: { message: 'Le consentement est obligatoire.' } } }
  if (mockUsers.find(u => u.email === data.email)) {
    throw { response: { status: 422, data: { message: 'Cet email est déjà utilisé.' } } }
  }
  const newUser: User & { password: string } = {
    id: mockUsers.length + 1,
    email: data.email,
    password: data.password,
    firstName: data.firstName,
    lastName: data.lastName,
    phone: data.phone ?? null,
    roles: ['ROLE_USER'],
    consentDate: new Date().toISOString(),
    consentVersion: '1.0',
    isAnonymized: false,
    createdAt: new Date().toISOString(),
  }
  mockUsers.push(newUser)
  return { message: 'Compte créé avec succès.' }
}

// ─── Me (RGPD) ────────────────────────────────────────────────────────────────

export async function mockGetMe(token: string): Promise<User> {
  await delay()
  const u = getUserFromToken(token)
  if (!u) throw { response: { status: 401, data: { message: 'Non authentifié.' } } }
  return stripPassword(u)
}

export async function mockUpdateMe(token: string, data: Partial<Pick<User, 'firstName' | 'lastName' | 'phone'>>) {
  await delay()
  const u = getUserFromToken(token)
  if (!u) throw { response: { status: 401 } }
  if (data.firstName !== undefined) u.firstName = data.firstName
  if (data.lastName !== undefined) u.lastName = data.lastName
  if (data.phone !== undefined) u.phone = data.phone
  return stripPassword(u)
}

export async function mockDeleteMe(token: string) {
  await delay()
  const u = getUserFromToken(token)
  if (!u) throw { response: { status: 401 } }
  u.firstName = 'Utilisateur supprimé'
  u.lastName = 'Utilisateur supprimé'
  u.email = `anon-${Math.random().toString(36).substring(2, 10)}@supprime.invalid`
  u.phone = null
  u.isAnonymized = true
  return { message: 'Compte anonymisé avec succès.' }
}

export async function mockUpdateConsent(token: string, granted: boolean) {
  await delay()
  const u = getUserFromToken(token)
  if (!u) throw { response: { status: 401 } }
  if (granted) {
    u.consentDate = new Date().toISOString()
    u.consentVersion = '1.0'
  }
  return { message: granted ? 'Consentement accordé.' : 'Consentement retiré.' }
}

// ─── Events ───────────────────────────────────────────────────────────────────

export async function mockGetEvents(): Promise<Event[]> {
  await delay()
  return mockEvents.filter(e => e.isPublished)
}

export async function mockGetEvent(id: number): Promise<Event> {
  await delay()
  const e = mockEvents.find(e => e.id === id)
  if (!e) throw { response: { status: 404, data: { message: 'Événement introuvable.' } } }
  return e
}

export async function mockCreateEvent(
  token: string,
  data: Omit<Event, 'id' | 'organizer' | 'createdAt' | 'registrationsCount'>,
): Promise<Event> {
  await delay()
  const u = getUserFromToken(token)
  if (!u) throw { response: { status: 401 } }
  const newEvent: Event = {
    ...data,
    id: nextEventId++,
    organizer: { id: u.id, firstName: u.firstName, lastName: u.lastName },
    createdAt: new Date().toISOString(),
    registrationsCount: 0,
  }
  mockEvents.push(newEvent)
  return newEvent
}

export async function mockUpdateEvent(token: string, id: number, data: Partial<Event>): Promise<Event> {
  await delay()
  const u = getUserFromToken(token)
  if (!u) throw { response: { status: 401 } }
  const idx = mockEvents.findIndex(e => e.id === id)
  if (idx === -1) throw { response: { status: 404 } }
  const existing = mockEvents[idx]!
  if (existing.organizer.id !== u.id) throw { response: { status: 403 } }
  const updated: Event = { ...existing, ...data }
  mockEvents[idx] = updated
  return updated
}

export async function mockDeleteEvent(token: string, id: number) {
  await delay()
  const u = getUserFromToken(token)
  if (!u) throw { response: { status: 401 } }
  const idx = mockEvents.findIndex(e => e.id === id)
  if (idx === -1) throw { response: { status: 404 } }
  const existing = mockEvents[idx]!
  if (existing.organizer.id !== u.id) throw { response: { status: 403 } }
  mockEvents.splice(idx, 1)
  return { message: 'Événement supprimé.' }
}

export async function mockGetMyEvents(token: string): Promise<Event[]> {
  await delay()
  const u = getUserFromToken(token)
  if (!u) throw { response: { status: 401 } }
  return mockEvents.filter(e => e.organizer.id === u.id)
}

// ─── Registrations ────────────────────────────────────────────────────────────

export async function mockRegisterToEvent(token: string, eventId: number): Promise<Registration> {
  await delay()
  const u = getUserFromToken(token)
  if (!u) throw { response: { status: 401 } }
  const event = mockEvents.find(e => e.id === eventId)
  if (!event) throw { response: { status: 404, data: { message: 'Événement introuvable.' } } }
  if (event.registrationsCount >= event.maxParticipants) {
    throw { response: { status: 422, data: { message: 'Cet événement est complet.' } } }
  }
  const existing = mockRegistrations.find(
    r => r.userId === u.id && r.eventId === eventId && r.status !== 'cancelled',
  )
  if (existing) throw { response: { status: 422, data: { message: 'Vous êtes déjà inscrit à cet événement.' } } }
  const reg: Registration = {
    id: nextRegistrationId++,
    userId: u.id,
    eventId,
    registeredAt: new Date().toISOString(),
    status: 'confirmed',
  }
  mockRegistrations.push(reg)
  event.registrationsCount++
  return reg
}

export async function mockCancelRegistration(token: string, registrationId: number) {
  await delay()
  const u = getUserFromToken(token)
  if (!u) throw { response: { status: 401 } }
  const reg = mockRegistrations.find(r => r.id === registrationId)
  if (!reg) throw { response: { status: 404 } }
  if (reg.userId !== u.id) throw { response: { status: 403 } }
  reg.status = 'cancelled'
  const event = mockEvents.find(e => e.id === reg.eventId)
  if (event && event.registrationsCount > 0) event.registrationsCount--
  return { message: 'Inscription annulée avec succès.' }
}

export async function mockGetMyRegistrations(token: string): Promise<Registration[]> {
  await delay()
  const u = getUserFromToken(token)
  if (!u) throw { response: { status: 401 } }
  return mockRegistrations
    .filter(r => r.userId === u.id)
    .map(r => ({ ...r, event: mockEvents.find(e => e.id === r.eventId) }))
}

export async function mockIsRegisteredToEvent(token: string, eventId: number): Promise<Registration | null> {
  await delay(100)
  const u = getUserFromToken(token)
  if (!u) return null
  return mockRegistrations.find(
    r => r.userId === u.id && r.eventId === eventId && r.status !== 'cancelled',
  ) ?? null
}
