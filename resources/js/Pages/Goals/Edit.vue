<template>
  <Head title="Edit Goal" />
  <AppLayout title="Edit Goal">
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        Edit Goal
      </h2>
    </template>
    <div class="py-10 mx-auto max-w-7xl sm:px-6 lg:px-8">
      <FormSection @submitted="submitForm()">
        <template #title> Goal Details </template>

        <template #description>
          Edit the details of the selected goal.
        </template>

        <template #form>
          <div class="col-span-6">
            <InputLabel for="goal_name" value="Goal Name" />
            <TextInput
              id="goal_name"
              v-model="form.goal_name"
              type="text"
              class="block w-full mt-1"
              autofocus
            />
            <InputError :message="form.errors.goal_name" class="mt-2" />
          </div>

          <div class="col-span-6 sm:col-span-4">
            <InputLabel for="goal_type" value="Goal Type" />
            <TextInput
              id="goal_type"
              v-model="form.goal_type"
              type="text"
              class="block w-full mt-1"
            />
            <InputError :message="form.errors.goal_type" class="mt-2" />
          </div>

          <div class="col-span-6 sm:col-span-4">
            <InputLabel for="goal_value" value="Goal Value" />
            <TextInput
              id="goal_value"
              v-model="form.goal_value"
              type="number"
              class="block w-full mt-1"
            />
            <InputError :message="form.errors.goal_value" class="mt-2" />
          </div>

          <div class="col-span-6 sm:col-span-4">
            <InputLabel for="project_code" value="Project" />
            <select
              id="project_code"
              v-model="form.project_code"
              class="block w-full mt-1 border-gray-300 rounded-md shadow-sm"
            >
              <option value="" disabled>Select a project</option>
              <option
                v-for="project in projects"
                :key="project.project_code"
                :value="project.project_code"
              >
                {{ project.project_name }}
              </option>
            </select>
            <InputError :message="form.errors.project_code" class="mt-2" />
          </div>

          <div class="col-span-6 mt-4">
            <InputLabel for="goal_description" value="Goal Description" />
            <textarea
              id="goal_description"
              v-model="form.goal_description"
              class="block w-full mt-1 border-gray-300 rounded-md shadow-sm"
              rows="3"
            ></textarea>
            <InputError :message="form.errors.goal_description" class="mt-2" />
          </div>
        </template>

        <template #actions>
          <PrimaryButton
            :class="{ 'opacity-25': form.processing }"
            :disabled="form.processing"
          >
            Update Goal
          </PrimaryButton>
        </template>
      </FormSection>
    </div>
  </AppLayout>
</template>

<script setup>
import { useForm } from "@inertiajs/vue3";
import FormSection from "@/Components/FormSection.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { defineProps } from "vue";

const props = defineProps({
  goal: Object,
  projects: Array,
});

const form = useForm({
  goal_name: props.goal.goal_name || "",
  goal_type: props.goal.goal_type || "",
  goal_value: props.goal.goal_value || "",
  goal_description: props.goal.goal_description || "",
  project_code: props.goal.project_code || "",
});

const submitForm = () => {
  form.put(route("goals.update", props.goal.id), {
    onSuccess: () => form.reset(),
    onError: () => alert("There was an error updating the goal"),
  });
};
</script>
