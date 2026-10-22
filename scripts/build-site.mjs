/**
 * Construit le site unifié servi à la racine du déploiement :
 *
 *   dist/              → page d'accueil (choix de l'application)
 *   dist/citoyen/      → application citoyenne
 *   dist/admin/        → interface agents CNLCEI
 *
 * Chaque application est construite avec APP_BASE pour que Vite et React
 * Router prennent en compte le sous-chemin.
 */
import { execFileSync } from 'node:child_process'
import { cp, mkdir, rm } from 'node:fs/promises'
import { dirname, join } from 'node:path'
import { fileURLToPath } from 'node:url'

const racine = join(dirname(fileURLToPath(import.meta.url)), '..')
const dist = join(racine, 'dist')

const apps = [
  { dossier: 'citizen-app', base: '/citoyen/', sortie: 'citoyen' },
  { dossier: 'admin-app', base: '/admin/', sortie: 'admin' },
]

const npm = process.platform === 'win32' ? 'npm.cmd' : 'npm'

function npmRun(args, cwd, env) {
  execFileSync(npm, args, { cwd, stdio: 'inherit', env: { ...process.env, ...env } })
}

await rm(dist, { recursive: true, force: true })
await mkdir(dist, { recursive: true })

for (const { dossier, base, sortie } of apps) {
  const cheminApp = join(racine, dossier)
  console.log(`\n▶ Construction de ${dossier} (base ${base})`)
  npmRun(['install'], cheminApp)
  npmRun(['run', 'build'], cheminApp, { APP_BASE: base })
  await cp(join(cheminApp, 'dist'), join(dist, sortie), { recursive: true })
}

// Page d'accueil + favicon partagé à la racine du site.
await cp(join(racine, 'landing'), dist, { recursive: true })
await cp(join(racine, 'citizen-app', 'public', 'favicon.svg'), join(dist, 'favicon.svg'))

console.log('\n✓ Site assemblé dans dist/')
