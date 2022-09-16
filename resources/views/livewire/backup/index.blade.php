<div>
    <x-slot name="header">
        <div class="flex items-center gap-1">
            <h2 class="font-semibold leading-tight">
                {{ __('Backups') }}
            </h2>
        </div>
    </x-slot>

    <div x-data="{ open: false }" class="container px-5 mx-auto">
        {{-- @php
            print '<pre>';
            print_r($liste);
            print '</pre>';
        @endphp --}}

        {{-- <button wire:click.prevent='reload' type="button" class="px-5 py-2 bg-blue-500 hover:bg-blue-600">reload</button> --}}

        <div class="flex justify-end gap-4 my-12">
            <button @click.prevent="open = true" type="button"
                class="flex items-center justify-center gap-2 px-5 py-2 text-gray-400 hover:text-primary-500 default-transition">
                <x:component::icon.setting />
            </button>

            @if ($connection)
                <button wire:click="createBackup" type="button"
                    class="flex items-center justify-center w-56 px-5 py-2 text-white border-0 rounded-md shadow-sm bg-primary-500 hover:text-white hover:bg-primary-600 default-transition">
                    MySQL Backup erstellen
                </button>
            @endif

        </div>

        <div class="w-full overflow-hidden">
            <div class="overflow-x-auto shadow md:rounded-lg">
                <div class="">

                    <x:component::table.wrapper>
                        <x-slot:head>
                            <x:component::table.row>
                                <x:component::table.cell class="w-1/12 font-semibold text-left text-gray-700">file
                                </x:component::table.cell>
                                <x:component::table.cell class="w-3/12 font-semibold text-left text-gray-700">created at
                                </x:component::table.cell>
                                <x:component::table.cell class="w-3/12 font-semibold text-left text-gray-700">size
                                </x:component::table.cell>
                                <x:component::table.cell></x:component::table.cell>
                            </x:component::table.row>
                        </x-slot:head>

                        <x-slot:body>

                            @foreach ($liste as $value)
                                <x:component::table.row class="hover:bg-gray-50">
                                    <x:component::table.cell class="py-4 pl-4 pr-3 text-sm whitespace-nowrap sm:pl-6">
                                        {{ $value['path'] }}
                                    </x:component::table.cell>

                                    <x:component::table.cell class="py-4 pl-4 pr-3 text-sm whitespace-nowrap sm:pl-6">
                                        {{ $value['created_at'] }}
                                    </x:component::table.cell>
                                    <x:component::table.cell class="py-4 pl-4 pr-3 text-sm whitespace-nowrap sm:pl-6">
                                        {{ $value['size'] }} MB
                                    </x:component::table.cell>

                                    <x:component::table.cell
                                        class="flex items-center justify-end gap-2 py-4 pl-3 pr-4 text-sm font-medium text-right whitespace-nowrap sm:pr-6">

                                        <a href="{{ route('package.backup.import', $value['path']) }}"
                                            class="flex items-center justify-center text-green-500 border-2 border-green-500 rounded-md shadow-sm hover:text-white w-9 h-9 hover:bg-green-600 default-transition">
                                            <x:component::icon.download class="rotate-180" />
                                        </a>

                                        <a href="{{ route('package.backup.download', $value['path']) }}"
                                            class="flex items-center justify-center border-2 rounded-md shadow-sm border-primary-500 text-primary-500 hover:text-white w-9 h-9 hover:bg-primary-600 default-transition">
                                            <x:component::icon.download />
                                        </a>

                                        <x:component::element.modal>
                                            <x-slot:trigger>

                                                <x:component::button.delete @click.prevent="modal=true" />

                                            </x-slot:trigger>

                                            <x-slot:content>
                                                <div class="flex justify-center ">
                                                    <div
                                                        class="flex items-center justify-center text-red-500 bg-red-200 rounded-full shadow-sm w-28 h-28">
                                                        <x:component::icon.delete class="h-16" />
                                                    </div>
                                                </div>
                                                <div class="flex justify-center mt-7">
                                                    <h3 class="text-lg font-bold text-center text-gray-700">
                                                        Unwiderruflich löschen?</h3>
                                                </div>
                                            </x-slot:content>

                                            <x-slot:controller>
                                                <button @click.prevent="modal=false" type="button"
                                                    class="flex justify-center w-full px-4 py-2 mr-2 font-medium text-center text-white bg-gray-300 border border-transparent rounded-md shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">Abbrechen</button>

                                                <button wire:click="delete('{{ $value['path'] }}')"
                                                    @click.prevent="modal=false" type="button"
                                                    class="flex justify-center w-full px-4 py-2 font-medium text-center text-white bg-red-500 border border-transparent rounded-md shadow-sm hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">löschen</button>
                                            </x-slot:controller>
                                        </x:component::element.modal>

                                    </x:component::table.cell>
                                </x:component::table.row>
                            @endforeach
                        </x-slot:body>

                    </x:component::table.wrapper>

                </div>
            </div>
        </div>

        <div x-cloak x-show="open"
            class="fixed top-0 bottom-0 left-0 right-0 z-50 flex items-center justify-center px-5 bg-gray-500 backdrop-blur-sm bg-opacity-70">
            <div class="overflow-hidden bg-white rounded-md shadow-sm w-96" @click.outside="open = false">
                <div class="px-5 py-7">
                    <div>
                        <x:component::form.label value="Host" />
                        <x:component::form.input wire:model="DBhost" type="text" id="host" />
                        <x:component::form.input-error :for="$DBhost" />
                    </div>
                    <div class="mt-5">
                        <x:component::form.label value="Database" />
                        <x:component::form.input wire:model="DBdatabase" type="text" id="database" />
                        <x:component::form.input-error :for="$DBdatabase" />
                    </div>
                    <div class="mt-5">
                        <x:component::form.label value="User" />
                        <x:component::form.input wire:model="DBuser" type="text" id="user" />
                        <x:component::form.input-error :for="$DBuser" />
                    </div>
                    <div class="mt-5">
                        <x:component::form.label value="Password" />
                        <x:component::form.input wire:model="DBpassword" type="password" id="password" />
                        <x:component::form.input-error :for="$DBpassword" />
                    </div>
                    <div class="mt-7">
                        @if (!empty($DBhost) && !empty($DBdatabase) && !empty($DBuser) && !empty($DBpassword))
                            <button wire:click="dbConnectingTest" type="button"
                                class="flex justify-center w-full px-4 py-2 mr-2 font-medium text-center text-white bg-blue-300 border border-transparent rounded-md shadow-sm hover:bg-blue-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Verbindung testen
                            </button>

                            @if ($connecting_result)
                                <p class="text-green-500 mt-7">Verbindung erfolgreich.</p>
                            @endif

                            @if ($connecting_result === false)
                                <p class="text-red-500 mt-7">Keine Verbindung mit der Datenbank.</p>
                            @endif
                        @endif
                    </div>

                </div>
                <div class="grid grid-cols-2 gap-4 px-4 text-right bg-gray-100 py-7 sm:px-6">
                    <button @click.prevent="open = false" type="button"
                        class="flex justify-center w-full px-4 py-2 mr-2 font-medium text-center text-white bg-gray-300 border border-transparent rounded-md shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">Abbrechen</button>

                    <button wire:click="settingsUpdate" @click.prevent="open = false" type="button"
                        class="flex justify-center w-full px-4 py-2 font-medium text-center text-white border border-transparent rounded-md shadow-sm bg-primary-500 hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">Speichern</button>
                </div>
            </div>
        </div>

    </div>
