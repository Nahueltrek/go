<script setup>
import { usePage, Link } from '@inertiajs/vue3'

const page = usePage()
const year = new Date().getFullYear()
</script>

<template>
  <footer class="bg-sky-950 text-sky-200 px-5 py-10 mt-auto">
    <Link href="/" class="flex items-center gap-2 mb-4">
      <img src="/images/logo.png" alt="GO Chile" class="h-8 w-8 rounded-full ring-1 ring-white/20" />
      <span class="text-sm font-bold text-white">GO Chile</span>
    </Link>
    <p class="text-xs text-sky-300 leading-relaxed max-w-xs mb-8">
      Descubre Chile desde sus territorios — rutas, experiencias, lugares y proyectos que conectan personas con la naturaleza.
    </p>

    <div class="grid grid-cols-2 gap-6 mb-8">
      <div>
        <h3 class="text-[11px] font-bold uppercase tracking-wide text-sky-400 mb-3">Explorar</h3>
        <ul class="space-y-2 text-xs">
          <li><Link href="/explorar" class="hover:text-white transition-colors">Explorar</Link></li>
          <li><Link href="/mapa" class="hover:text-white transition-colors">Mapa</Link></li>
          <li><Link href="/explorar?type=experiencia" class="hover:text-white transition-colors">Experiencias</Link></li>
          <li><Link href="/explorar?type=proyecto" class="hover:text-white transition-colors">Proyectos</Link></li>
          <li><Link href="/explorar?type=operador" class="hover:text-white transition-colors">Organizaciones</Link></li>
          <li><Link href="/agenda" class="hover:text-white transition-colors">Agenda</Link></li>
          <li><Link href="/bitacora" class="hover:text-white transition-colors">Bitácora</Link></li>
        </ul>
      </div>
      <div>
        <h3 class="text-[11px] font-bold uppercase tracking-wide text-sky-400 mb-3">Participa</h3>
        <ul class="space-y-2 text-xs">
          <li><Link href="/colaboradores" class="hover:text-white transition-colors">Quiero ser parte</Link></li>
          <li v-if="page.props.auth?.user"><Link href="/mi-organizacion" class="hover:text-white transition-colors">Mi GO</Link></li>
        </ul>
      </div>
    </div>

    <p class="text-[11px] text-sky-500 border-t border-white/10 pt-4">
      © {{ year }} GO Chile
    </p>
  </footer>
</template>
