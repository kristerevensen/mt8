<template>

    <Head title="Projects" />
    <AppLayout title="Projects">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Projects
            </h2>
        </template>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-0">
                <Projects />
            </div>
        </div>


    </AppLayout>

</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { defineProps } from 'vue';
import Projects from '@/Pages/Projects/components/Projects.vue';
const props = defineProps({
    projects: Array,
});

const projects = props.projects;

</script>
