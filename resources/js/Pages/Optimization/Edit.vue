<script setup>
import { ref, onMounted } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import FormSection from "@/Components/FormSection.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import Breadcrumbs from "@/Components/Breadcrumbs.vue";

// Props for the form data
const props = defineProps({
  category: Object,
});

// Breadcrumbs for navigation
const breadcrumbs = [
  { name: "Growth", href: "/growth", current: false },
  { name: "Optimization Criteria", href: "/optimization", current: false },
  { name: "Edit Category", current: true },
];

// Initialize form data with existing category information
const form = useForm({
  category_name: props.category.category_name,
  project_code: props.category.project_code,
});

// Submit the form to update the category
const submitForm = () => {
  form.put(`/optimization/${props.category.category_uuid}`, {
    onError: () => alert("There was an error updating the category"),
  });
};
</script>

<template>
  <Head title="Edit Category" />
  <AppLayout title="Edit Category">
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        <Breadcrumbs :pages="breadcrumbs" />
      </h2>
    </template>
    <div class="py-10 mx-auto max-w-7xl sm:px-6 lg:px-8">
      <FormSection @submitted="submitForm()">
        <template #title> Edit Category Details </template>
        <template #description>
          Update the details for the optimization category.
        </template>

        <template #form>
          <div class="col-span-6">
            <InputLabel for="category_name" value="Category Name" />
            <TextInput
              id="category_name"
              v-model="form.category_name"
              type="text"
              class="block w-full mt-1"
              autofocus
            />
            <InputError :message="form.errors.category_name" class="mt-2" />
          </div>
        </template>

        <template #actions>
          <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
            Update Category
          </PrimaryButton>
        </template>
      </FormSection>
    </div>
  </AppLayout>
</template>
