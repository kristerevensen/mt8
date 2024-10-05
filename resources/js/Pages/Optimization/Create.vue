<script setup>
import { Head } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import { useForm } from "@inertiajs/vue3";
import FormSection from "@/Components/FormSection.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import Breadcrumbs from "@/Components/Breadcrumbs.vue";

// Breadcrumbs for navigation
const breadcrumbs = [
  { name: "Growth", href: "/growth", current: false },
  { name: "Optimization Criteria", href: "/optimization", current: false },
  { name: "Create Category", current: true },
];

// Initialize form data
const form = useForm({
  category_name: "",
  project_code: "",
});

// Submit the form to create a new category
const submitForm = () => {
  form.post("/optimization", {
    onSuccess: () => form.reset(),
    onError: () => alert("There was an error creating the category"),
  });
};
</script>

<template>
  <Head title="Create Optimization Category" />
  <AppLayout title="Create Optimization Category">
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        <Breadcrumbs :pages="breadcrumbs" />
      </h2>
    </template>
    <div class="py-10 mx-auto max-w-7xl sm:px-6 lg:px-8">
      <FormSection @submitted="submitForm()">
        <template #title> Category Details </template>
        <template #description>
          Fill in the details for the new optimization category.<br /><p><b>Use comma for creating several in one creation</b></p>
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
            Create Category
          </PrimaryButton>
        </template>
      </FormSection>
    </div>
  </AppLayout>
</template>
