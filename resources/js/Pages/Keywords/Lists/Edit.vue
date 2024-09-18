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

const props = defineProps({
  keywordList: Object, // Expecting the keyword list object to be passed in as a prop
});

const breadcrumbs = [
  { name: "Keywords", href: "/keywords", current: false },
  { name: "Keyword Lists", href: "/keywords/lists", current: false },
  { name: `Edit ${props.keywordList.name}`, current: true },
];

const form = useForm({
  name: props.keywordList.name,
  description: props.keywordList.description,
});

const submitForm = () => {
  form.put(route("keyword-lists.update", props.keywordList.list_uuid), {
    onError: () => alert("There was an error updating the keyword list"),
  });
};
</script>

<template>
  <Head :title="`Edit ${props.keywordList.name}`" />
  <AppLayout :title="`Edit ${props.keywordList.name}`">
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        <Breadcrumbs :pages="breadcrumbs" />
      </h2>
    </template>
    <div class="py-10 mx-auto max-w-7xl sm:px-6 lg:px-8">
      <FormSection @submitted="submitForm()">
        <template #title> Edit Keyword List </template>

        <template #description>
          Update the details for this keyword list.
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
            Update Keyword List
          </PrimaryButton>
        </template>
      </FormSection>
    </div>
  </AppLayout>
</template>
