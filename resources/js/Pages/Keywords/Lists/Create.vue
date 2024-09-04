<script setup>
import { Head, Link } from "@inertiajs/vue3";

import AppLayout from "@/Layouts/AppLayout.vue";
import { useForm } from "@inertiajs/vue3";
import FormSection from "@/Components/FormSection.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import Breadcrumbs from "@/Components/Breadcrumbs.vue";

import { defineProps } from "vue";

const props = defineProps({});

const breadcrumbs = [
  { name: "Keywords", href: "/keywords", current: false },
  { name: "Keyword Lists", href: "/keywords/lists", current: false },
  { name: "Create Keyword List", current: true },
];

const form = useForm({
  name: "",
  description: "",
  project_code: "",
});

const submitForm = () => {
  form.post(route("keyword-lists.store"), {
    onSuccess: () => form.reset(),
    onError: () => alert("There was an error creating the keyword list"),
  });
};
</script>

<template>
  <Head title="Create Keyword List" />
  <AppLayout title="Create Keyword List">
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        <Breadcrumbs :pages="breadcrumbs" />
      </h2>
    </template>
    <div class="py-10 mx-auto max-w-7xl sm:px-6 lg:px-8">
      <FormSection @submitted="submitForm()">
        <template #title> Keyword List Details </template>

        <template #description>
          Fill in the details for the new keyword list.
        </template>

        <template #form>
          <div class="col-span-6">
            <InputLabel for="name" value="List Name" />
            <TextInput
              id="name"
              v-model="form.name"
              type="text"
              class="block w-full mt-1"
              autofocus
            />
            <InputError :message="form.errors.name" class="mt-2" />
          </div>

          <div class="col-span-6 mt-4">
            <InputLabel for="description" value="Description" />
            <textarea
              id="description"
              v-model="form.description"
              class="block w-full mt-1 border-gray-300 rounded-md shadow-sm"
              rows="3"
            ></textarea>
            <InputError :message="form.errors.description" class="mt-2" />
          </div>
        </template>

        <template #actions>
          <PrimaryButton
            :class="{ 'opacity-25': form.processing }"
            :disabled="form.processing"
          >
            Create Keyword List
          </PrimaryButton>
        </template>
      </FormSection>
    </div>
  </AppLayout>
</template>
