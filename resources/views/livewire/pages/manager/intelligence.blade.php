<?php
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;

new #[Layout('layouts.app')] class extends Component {
    //
}; ?>

<div class="h-[calc(100vh-65px)] overflow-hidden flex flex-col">
    <x-slot name="header">
        <h2 class="font-bold text-xl text-gray-800 leading-tight">
            {{ __('Inteligencia de Negocio') }}
        </h2>
    </x-slot>

    <div class="flex-1 py-4 px-4 sm:px-6 lg:px-8 overflow-y-auto">
        <div class="max-w-[1920px] mx-auto h-full flex flex-col justify-center">
            
            <div class="mb-4 text-center">
                <p class="text-gray-600 text-base">
                    Tableros de control y KPIs en tiempo real.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6">
                
                <!-- Ventas -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-md hover:shadow-xl transition-all duration-300 group flex flex-col">
                    <div class="h-20 bg-gradient-to-r from-blue-500 to-blue-600 relative overflow-hidden shrink-0">
                        <div class="absolute inset-0 opacity-20 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMSIgY3k9IjEiIHI9IjEiIGZpbGw9IiNmZmYiLz48L3N2Zz4=')]"></div>
                        <div class="absolute bottom-0 left-0 p-3 flex items-center space-x-3">
                            <div class="p-1.5 bg-white/20 backdrop-blur-sm rounded-lg">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold text-white leading-none">Ventas</h3>
                        </div>
                    </div>
                    <div class="p-4 flex-1 flex flex-col justify-between">
                        <p class="text-gray-500 text-xs mb-3 line-clamp-2">Ingresos, objetivos y rendimiento comercial.</p>
                        <button disabled class="w-full py-1.5 px-3 bg-gray-50 text-gray-400 text-xs font-semibold rounded-lg cursor-not-allowed flex items-center justify-between border border-gray-100">
                            <span>Próximamente</span>
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Presupuestos -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-md hover:shadow-xl transition-all duration-300 group flex flex-col">
                    <div class="h-20 bg-gradient-to-r from-purple-500 to-purple-600 relative overflow-hidden shrink-0">
                        <div class="absolute inset-0 opacity-20 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMSIgY3k9IjEiIHI9IjEiIGZpbGw9IiNmZmYiLz48L3N2Zz4=')]"></div>
                        <div class="absolute bottom-0 left-0 p-3 flex items-center space-x-3">
                            <div class="p-1.5 bg-white/20 backdrop-blur-sm rounded-lg">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold text-white leading-none">Presupuestos</h3>
                        </div>
                    </div>
                    <div class="p-4 flex-1 flex flex-col justify-between">
                        <p class="text-gray-500 text-xs mb-3 line-clamp-2">Costos, márgenes y proyección financiera.</p>
                        <button disabled class="w-full py-1.5 px-3 bg-gray-50 text-gray-400 text-xs font-semibold rounded-lg cursor-not-allowed flex items-center justify-between border border-gray-100">
                            <span>Próximamente</span>
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Horas -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-md hover:shadow-xl transition-all duration-300 group flex flex-col">
                    <div class="h-20 bg-gradient-to-r from-green-500 to-green-600 relative overflow-hidden shrink-0">
                        <div class="absolute inset-0 opacity-20 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMSIgY3k9IjEiIHI9IjEiIGZpbGw9IiNmZmYiLz48L3N2Zz4=')]"></div>
                        <div class="absolute bottom-0 left-0 p-3 flex items-center space-x-3">
                            <div class="p-1.5 bg-white/20 backdrop-blur-sm rounded-lg">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold text-white leading-none">Horas</h3>
                        </div>
                    </div>
                    <div class="p-4 flex-1 flex flex-col justify-between">
                        <p class="text-gray-500 text-xs mb-3 line-clamp-2">Eficiencia, carga de trabajo y rendimiento.</p>
                        <button disabled class="w-full py-1.5 px-3 bg-gray-50 text-gray-400 text-xs font-semibold rounded-lg cursor-not-allowed flex items-center justify-between border border-gray-100">
                            <span>Próximamente</span>
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Compras -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-md hover:shadow-xl transition-all duration-300 group flex flex-col">
                    <div class="h-20 bg-gradient-to-r from-orange-500 to-orange-600 relative overflow-hidden shrink-0">
                        <div class="absolute inset-0 opacity-20 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMSIgY3k9IjEiIHI9IjEiIGZpbGw9IiNmZmYiLz48L3N2Zz4=')]"></div>
                        <div class="absolute bottom-0 left-0 p-3 flex items-center space-x-3">
                            <div class="p-1.5 bg-white/20 backdrop-blur-sm rounded-lg">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold text-white leading-none">Compras</h3>
                        </div>
                    </div>
                    <div class="p-4 flex-1 flex flex-col justify-between">
                        <p class="text-gray-500 text-xs mb-3 line-clamp-2">Proveedores, precios e inventario.</p>
                        <button disabled class="w-full py-1.5 px-3 bg-gray-50 text-gray-400 text-xs font-semibold rounded-lg cursor-not-allowed flex items-center justify-between border border-gray-100">
                            <span>Próximamente</span>
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Tableros -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-md hover:shadow-xl transition-all duration-300 group flex flex-col">
                    <div class="h-20 bg-gradient-to-r from-indigo-500 to-indigo-600 relative overflow-hidden shrink-0">
                        <div class="absolute inset-0 opacity-20 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMSIgY3k9IjEiIHI9IjEiIGZpbGw9IiNmZmYiLz48L3N2Zz4=')]"></div>
                        <div class="absolute bottom-0 left-0 p-3 flex items-center space-x-3">
                            <div class="p-1.5 bg-white/20 backdrop-blur-sm rounded-lg">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold text-white leading-none">Tableros</h3>
                        </div>
                    </div>
                    <div class="p-4 flex-1 flex flex-col justify-between">
                        <p class="text-gray-500 text-xs mb-3 line-clamp-2">Proyectos, hitos y planificación.</p>
                        <button disabled class="w-full py-1.5 px-3 bg-gray-50 text-gray-400 text-xs font-semibold rounded-lg cursor-not-allowed flex items-center justify-between border border-gray-100">
                            <span>Próximamente</span>
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Automatización -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-md hover:shadow-xl transition-all duration-300 group flex flex-col">
                    <div class="h-20 bg-gradient-to-r from-red-500 to-red-600 relative overflow-hidden shrink-0">
                        <div class="absolute inset-0 opacity-20 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMSIgY3k9IjEiIHI9IjEiIGZpbGw9IiNmZmYiLz48L3N2Zz4=')]"></div>
                        <div class="absolute bottom-0 left-0 p-3 flex items-center space-x-3">
                            <div class="p-1.5 bg-white/20 backdrop-blur-sm rounded-lg">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold text-white leading-none">Automatización</h3>
                        </div>
                    </div>
                    <div class="p-4 flex-1 flex flex-col justify-between">
                        <p class="text-gray-500 text-xs mb-3 line-clamp-2">Ahorro de tiempo y eficiencia de procesos.</p>
                        <button disabled class="w-full py-1.5 px-3 bg-gray-50 text-gray-400 text-xs font-semibold rounded-lg cursor-not-allowed flex items-center justify-between border border-gray-100">
                            <span>Próximamente</span>
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Satisfacción Personal (Empleados) -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-md hover:shadow-xl transition-all duration-300 group flex flex-col">
                    <div class="h-20 bg-gradient-to-r from-teal-500 to-teal-600 relative overflow-hidden shrink-0">
                        <div class="absolute inset-0 opacity-20 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMSIgY3k9IjEiIHI9IjEiIGZpbGw9IiNmZmYiLz48L3N2Zz4=')]"></div>
                        <div class="absolute bottom-0 left-0 p-3 flex items-center space-x-3">
                            <div class="p-1.5 bg-white/20 backdrop-blur-sm rounded-lg">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold text-white leading-none">Sat. Personal</h3>
                        </div>
                    </div>
                    <div class="p-4 flex-1 flex flex-col justify-between">
                        <p class="text-gray-500 text-xs mb-3 line-clamp-2">Clima laboral, bienestar y retención de talento.</p>
                        <button disabled class="w-full py-1.5 px-3 bg-gray-50 text-gray-400 text-xs font-semibold rounded-lg cursor-not-allowed flex items-center justify-between border border-gray-100">
                            <span>Próximamente</span>
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Satisfacción Cliente -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-md hover:shadow-xl transition-all duration-300 group flex flex-col">
                    <div class="h-20 bg-gradient-to-r from-pink-500 to-pink-600 relative overflow-hidden shrink-0">
                        <div class="absolute inset-0 opacity-20 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMSIgY3k9IjEiIHI9IjEiIGZpbGw9IiNmZmYiLz48L3N2Zz4=')]"></div>
                        <div class="absolute bottom-0 left-0 p-3 flex items-center space-x-3">
                            <div class="p-1.5 bg-white/20 backdrop-blur-sm rounded-lg">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold text-white leading-none">Sat. Clientes</h3>
                        </div>
                    </div>
                    <div class="p-4 flex-1 flex flex-col justify-between">
                        <p class="text-gray-500 text-xs mb-3 line-clamp-2">NPS, feedback y fidelización de clientes.</p>
                        <button disabled class="w-full py-1.5 px-3 bg-gray-50 text-gray-400 text-xs font-semibold rounded-lg cursor-not-allowed flex items-center justify-between border border-gray-100">
                            <span>Próximamente</span>
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
