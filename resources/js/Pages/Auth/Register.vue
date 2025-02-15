<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    terms: false,
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Opprett konto" />

    <div class="min-h-screen flex">
        <!-- Left Side - Registration Form -->
        <div class="w-full lg:w-1/2 flex items-center justify-center p-8 bg-white dark:bg-gray-50">
            <div class="w-full max-w-md space-y-8">
                <!-- Logo and Header -->
                <div class="text-center">
                  <!-- Measuretank logo som tekst -->
                    <h1 class="text-4xl font-bold text-gray-900">Measuretank</h1>
                </div>




                <!-- Registration Form -->
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Name Field -->
                    <div>
                        <TextInput
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="block w-full px-4 py-3 rounded-lg border border-gray-300 text-gray-900 placeholder-gray-500 focus:border-gray-500 focus:ring-gray-500"
                            required
                            placeholder="Navn"
                            autofocus
                            autocomplete="name"
                        />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>

                    <!-- Email Field -->
                    <div>
                        <TextInput
                            id="email"
                            v-model="form.email"
                            type="email"
                            class="block w-full px-4 py-3 rounded-lg border border-gray-300 text-gray-900 placeholder-gray-500 focus:border-gray-500 focus:ring-gray-500"
                            required
                            placeholder="E-post"
                            autocomplete="username"
                        />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <!-- Password Field -->
                    <div>
                        <TextInput
                            id="password"
                            v-model="form.password"
                            type="password"
                            class="block w-full px-4 py-3 rounded-lg border border-gray-300 text-gray-900 placeholder-gray-500 focus:border-gray-500 focus:ring-gray-500"
                            required
                            placeholder="Passord"
                            autocomplete="new-password"
                        />
                        <InputError class="mt-2" :message="form.errors.password" />
                    </div>

                    <!-- Confirm Password Field -->
                    <div>
                        <TextInput
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                            class="block w-full px-4 py-3 rounded-lg border border-gray-300 text-gray-900 placeholder-gray-500 focus:border-gray-500 focus:ring-gray-500"
                            required
                            placeholder="Bekreft passord"
                            autocomplete="new-password"
                        />
                        <InputError class="mt-2" :message="form.errors.password_confirmation" />
                    </div>

                    <!-- Terms and Conditions -->
                    <div v-if="$page.props.jetstream.hasTermsAndPrivacyPolicyFeature" class="flex items-start">
                        <div class="flex items-center h-5">
                            <input
                                id="terms"
                                v-model="form.terms"
                                name="terms"
                                type="checkbox"
                                class="h-4 w-4 rounded border-gray-300 text-gray-900 focus:ring-gray-500"
                                required
                            />
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="terms" class="text-gray-600">
                                Jeg godtar <a target="_blank" :href="route('terms.show')" class="font-medium text-gray-900 hover:text-gray-700">vilkårene</a> og <a target="_blank" :href="route('policy.show')" class="font-medium text-gray-900 hover:text-gray-700">personvernerklæringen</a>
                            </label>
                            <InputError class="mt-2" :message="form.errors.terms" />
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-gray-900 hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
                    >
                        <span v-if="form.processing">Oppretter konto...</span>
                        <span v-else>Opprett konto</span>
                    </button>

                    <!-- Login Link -->
                    <p class="text-center text-sm text-gray-600">
                        Har du allerede en konto?
                        <Link 
                            :href="route('login')" 
                            class="font-medium text-gray-900 hover:text-gray-700"
                        >
                            Logg inn her
                        </Link>
                    </p>
                </form>
            </div>
        </div>

        <!-- Right Side - Dark Theme with Quote -->
        <div class="hidden lg:flex lg:w-1/2 relative bg-gray-900">
            <div class="relative w-full h-full flex items-center justify-center p-12">
                <div class="max-w-md text-center text-white">
                    <p class="text-lg font-medium mb-2">
                        "Beyond UI: It's the design equivalent of discovering the theory of relativity for your creativity."
                    </p>
                    <p class="text-sm text-gray-400">- Albert Einstein</p>
                </div>
            </div>
            
            <!-- Background Pattern -->
            <div class="absolute inset-0 bg-[radial-gradient(#ffffff33_1px,#00091d_1px)] bg-[size:20px_20px]"></div>
        </div>
    </div>
</template>