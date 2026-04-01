<script setup lang="ts">
const dataTable = [
  { data: 'Prénom, Nom', purpose: 'Identification, personnalisation', required: true },
  { data: 'Email', purpose: 'Authentification, notifications', required: true },
  { data: 'Mot de passe (hashé)', purpose: 'Sécurité du compte', required: true },
  { data: 'Téléphone', purpose: 'Contact optionnel', required: false },
  { data: 'Date de consentement', purpose: 'Preuve de consentement RGPD', required: true },
  { data: 'Inscriptions aux événements', purpose: 'Gestion des participations', required: true },
  { data: 'Adresse IP (hash SHA-256)', purpose: 'Logs de consentement, sécurité', required: true },
]

const legalBases = [
  { icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', title: 'Consentement (Art. 6.1.a)', description: 'Traitement des cookies non essentiels, communications marketing.' },
  { icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', title: 'Exécution du contrat (Art. 6.1.b)', description: 'Gestion du compte, inscriptions aux événements, notifications.' },
  { icon: 'M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3', title: 'Obligation légale (Art. 6.1.c)', description: 'Conservation des logs de consentement, données de facturation.' },
  { icon: 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z', title: 'Intérêt légitime (Art. 6.1.f)', description: 'Sécurité du service, prévention de la fraude, statistiques.' },
]

const rights = [
  { icon: 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z', name: "Droit d'accès", description: 'Obtenir une copie de toutes vos données stockées.' },
  { icon: 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z', name: 'Droit de rectification', description: 'Corriger vos données inexactes ou incomplètes.' },
  { icon: 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16', name: "Droit à l'effacement", description: 'Demander l\'anonymisation de votre compte.' },
  { icon: 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z', name: "Droit d'opposition", description: 'Vous opposer à certains traitements de vos données.' },
  { icon: 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4', name: 'Droit à la portabilité', description: 'Recevoir vos données dans un format structuré.' },
  { icon: 'M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z', name: 'Droit à la limitation', description: 'Restreindre temporairement le traitement.' },
]
</script>

<template>
  <div class="max-w-3xl mx-auto px-4 py-12">
    <!-- Header -->
    <div class="mb-10">
      <span class="inline-flex items-center gap-2 text-xs font-semibold text-primary-600 bg-primary-50 px-3 py-1.5 rounded-full mb-4">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
        </svg>
        Conforme RGPD — Règlement (UE) 2016/679
      </span>
      <h1 class="text-4xl font-extrabold text-gray-900 mb-3">Politique de confidentialité</h1>
      <p class="text-gray-500">Dernière mise à jour : <strong>15 janvier 2026</strong> — Version 1.0</p>
    </div>

    <div class="space-y-8 text-gray-700 leading-relaxed">

      <!-- 1 -->
      <section class="bg-white rounded-2xl border border-gray-100 shadow-sm p-7">
        <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
          <span class="w-7 h-7 bg-primary-100 text-primary-700 rounded-lg flex items-center justify-center text-sm font-extrabold">1</span>
          Responsable du traitement
        </h2>
        <p>
          Le responsable du traitement des données est <strong>EventFlow SAS</strong>, dont le siège social est situé
          au 12 rue de la République, 75001 Paris.
        </p>
        <p class="mt-3">
          <strong>DPO (Délégué à la Protection des Données) :</strong> Sophie Lemaire —
          <a href="mailto:dpo@eventflow.fr" class="text-primary-600 underline">dpo@eventflow.fr</a>
        </p>
      </section>

      <!-- 2 -->
      <section class="bg-white rounded-2xl border border-gray-100 shadow-sm p-7">
        <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
          <span class="w-7 h-7 bg-primary-100 text-primary-700 rounded-lg flex items-center justify-center text-sm font-extrabold">2</span>
          Données collectées
        </h2>
        <p class="mb-3">Dans le cadre de l'utilisation de la plateforme EventFlow, nous collectons les données suivantes :</p>
        <div class="overflow-x-auto">
          <table class="w-full text-sm border-collapse">
            <thead>
              <tr class="bg-gray-50">
                <th class="text-left p-3 border border-gray-200 font-semibold text-gray-700">Donnée</th>
                <th class="text-left p-3 border border-gray-200 font-semibold text-gray-700">Finalité</th>
                <th class="text-left p-3 border border-gray-200 font-semibold text-gray-700">Obligatoire</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="row in dataTable" :key="row.data" class="hover:bg-gray-50">
                <td class="p-3 border border-gray-200 font-medium">{{ row.data }}</td>
                <td class="p-3 border border-gray-200 text-gray-600">{{ row.purpose }}</td>
                <td class="p-3 border border-gray-200">
                  <span :class="row.required ? 'text-red-600 font-semibold' : 'text-gray-400'">
                    {{ row.required ? 'Oui' : 'Non' }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <!-- 3 -->
      <section class="bg-white rounded-2xl border border-gray-100 shadow-sm p-7">
        <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
          <span class="w-7 h-7 bg-primary-100 text-primary-700 rounded-lg flex items-center justify-center text-sm font-extrabold">3</span>
          Bases légales des traitements
        </h2>
        <div class="space-y-3">
          <div v-for="t in legalBases" :key="t.title" class="flex gap-3 p-3 bg-gray-50 rounded-xl">
            <svg class="w-5 h-5 shrink-0 mt-0.5 text-primary-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" :d="t.icon" />
            </svg>
            <div>
              <p class="font-semibold text-gray-800 text-sm">{{ t.title }}</p>
              <p class="text-sm text-gray-500 mt-0.5">{{ t.description }}</p>
            </div>
          </div>
        </div>
      </section>

      <!-- 4 -->
      <section class="bg-white rounded-2xl border border-gray-100 shadow-sm p-7">
        <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
          <span class="w-7 h-7 bg-primary-100 text-primary-700 rounded-lg flex items-center justify-center text-sm font-extrabold">4</span>
          Durées de conservation
        </h2>
        <ul class="space-y-2 text-sm">
          <li class="flex gap-2"><span class="text-primary-500 font-bold">•</span> Données de compte actif : <strong>durée de l'utilisation du service + 3 ans</strong></li>
          <li class="flex gap-2"><span class="text-primary-500 font-bold">•</span> Données après inactivité de 2 ans : <strong>anonymisation automatique</strong></li>
          <li class="flex gap-2"><span class="text-primary-500 font-bold">•</span> Logs de consentement (ConsentLog) : <strong>5 ans (obligation légale)</strong></li>
          <li class="flex gap-2"><span class="text-primary-500 font-bold">•</span> Cookies analytiques : <strong>13 mois maximum</strong></li>
          <li class="flex gap-2"><span class="text-primary-500 font-bold">•</span> Données d'inscription aux événements : <strong>3 ans après l'événement</strong></li>
        </ul>
      </section>

      <!-- 5 -->
      <section class="bg-white rounded-2xl border border-gray-100 shadow-sm p-7">
        <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
          <span class="w-7 h-7 bg-primary-100 text-primary-700 rounded-lg flex items-center justify-center text-sm font-extrabold">5</span>
          Vos droits
        </h2>
        <p class="mb-4 text-sm">Conformément au RGPD, vous disposez des droits suivants :</p>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
          <div v-for="right in rights" :key="right.name" class="p-4 bg-gray-50 rounded-xl border border-gray-200">
            <p class="font-bold text-gray-800 text-sm flex items-center gap-2">
              <svg class="w-4 h-4 shrink-0 text-primary-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" :d="right.icon" />
              </svg>
              {{ right.name }}
            </p>
            <p class="text-xs text-gray-500 mt-1">{{ right.description }}</p>
          </div>
        </div>
        <p class="mt-5 text-sm">
          Pour exercer vos droits, connectez-vous et accédez à
          <router-link to="/profile" class="text-primary-600 underline font-medium">Mon profil</router-link>,
          ou contactez notre DPO à
          <a href="mailto:dpo@eventflow.fr" class="text-primary-600 underline">dpo@eventflow.fr</a>.
        </p>
      </section>

      <!-- 6 -->
      <section class="bg-white rounded-2xl border border-gray-100 shadow-sm p-7">
        <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
          <span class="w-7 h-7 bg-primary-100 text-primary-700 rounded-lg flex items-center justify-center text-sm font-extrabold">6</span>
          Cookies
        </h2>
        <p class="text-sm mb-4">
          Notre site utilise trois catégories de cookies. Vous pouvez gérer vos préférences via le bandeau
          qui s'affiche à votre première visite.
        </p>
        <div class="space-y-3 text-sm">
          <div class="flex gap-3 p-3 bg-green-50 rounded-xl border border-green-100">
            <svg class="w-5 h-5 shrink-0 mt-0.5 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
            <div>
              <p class="font-semibold text-gray-800">Nécessaires</p>
              <p class="text-gray-500">Session, authentification JWT. <em>Toujours actifs, pas de consentement requis.</em></p>
            </div>
          </div>
          <div class="flex gap-3 p-3 bg-blue-50 rounded-xl border border-blue-100">
            <svg class="w-5 h-5 shrink-0 mt-0.5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            <div>
              <p class="font-semibold text-gray-800">Analytiques</p>
              <p class="text-gray-500">Statistiques de navigation anonymisées. Soumis à votre consentement.</p>
            </div>
          </div>
          <div class="flex gap-3 p-3 bg-yellow-50 rounded-xl border border-yellow-100">
            <svg class="w-5 h-5 shrink-0 mt-0.5 text-yellow-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
            </svg>
            <div>
              <p class="font-semibold text-gray-800">Marketing</p>
              <p class="text-gray-500">Personnalisation des contenus. Soumis à votre consentement.</p>
            </div>
          </div>
        </div>
      </section>

      <!-- 7 -->
      <section class="bg-white rounded-2xl border border-gray-100 shadow-sm p-7">
        <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
          <span class="w-7 h-7 bg-primary-100 text-primary-700 rounded-lg flex items-center justify-center text-sm font-extrabold">7</span>
          Sécurité & Pseudonymisation
        </h2>
        <ul class="space-y-2 text-sm">
          <li class="flex gap-2"><span class="text-primary-500 font-bold">•</span> Mots de passe hashés avec <strong>bcrypt</strong> (facteur de coût 12)</li>
          <li class="flex gap-2"><span class="text-primary-500 font-bold">•</span> Authentification par <strong>JWT signé</strong> (expiration 1h)</li>
          <li class="flex gap-2"><span class="text-primary-500 font-bold">•</span> Adresses IP stockées sous forme de <strong>hash SHA-256</strong></li>
          <li class="flex gap-2"><span class="text-primary-500 font-bold">•</span> Communications chiffrées en <strong>HTTPS/TLS</strong></li>
          <li class="flex gap-2"><span class="text-primary-500 font-bold">•</span> Anonymisation automatique après <strong>2 ans d'inactivité</strong></li>
        </ul>
      </section>

      <!-- Contact -->
      <section class="bg-primary-50 rounded-2xl border border-primary-100 p-7">
        <h2 class="text-lg font-bold text-primary-900 mb-2">Contact & Réclamations</h2>
        <p class="text-sm text-primary-800">
          Pour toute question relative à vos données personnelles, contactez notre DPO :
          <a href="mailto:dpo@eventflow.fr" class="font-semibold underline">dpo@eventflow.fr</a>.
          <br /><br />
          Vous avez également le droit d'introduire une réclamation auprès de la
          <a href="https://www.cnil.fr" target="_blank" rel="noopener" class="font-semibold underline">CNIL</a>
          (Commission Nationale de l'Informatique et des Libertés).
        </p>
      </section>
    </div>
  </div>
</template>
