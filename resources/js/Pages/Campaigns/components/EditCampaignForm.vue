<script setup>
import { useForm } from "@inertiajs/vue3";
import FormSection from "@/Components/FormSection.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { defineProps } from "vue";

// Define props to accept the campaign and projects from the parent component
const props = defineProps({
  campaign: Object,
  projects: Array,
});

// Initialize form data using useForm hook from Inertia with pre-filled data
const form = useForm({
  campaign_name: props.campaign.campaign_name || "",
  project_code: props.campaign.project_code || "",
  start: props.campaign.start
    ? new Date(props.campaign.start).toISOString().slice(0, -1)
    : "",
  end: props.campaign.end
    ? new Date(props.campaign.end).toISOString().slice(0, -1)
    : "",
  status: props.campaign.status ? 1 : 0,
  reporting: props.campaign.reporting ? true : false,
  force_lowercase: props.campaign.force_lowercase ? true : false,
  utm_activated: props.campaign.utm_activated ? true : false,
  monitor_urls: props.campaign.monitor_urls ? true : false,
  description: props.campaign.description || "",
});
const submitForm = () => {
  form.put(route("campaigns.update", props.campaign.id), {
    onSuccess: () => form.reset(),
    onError: () => alert("There was an error updating the campaign"),
  });
};
</script>

<template>
  <FormSection @submitted="submitForm()">
    <template #title> Campaign Details </template>

    <template #description>
      Edit the details of the selected campaign.
    </template>

    <template #form>
      <div class="col-span-6">
        <InputLabel for="campaign_name" value="Campaign Name" />
        <TextInput
          id="campaign_name"
          v-model="form.campaign_name"
          type="text"
          class="block w-full mt-1"
          autofocus
        />
        <InputError :message="form.errors.campaign_name" class="mt-2" />
      </div>

      <!-- Start and End Dates -->
      <div class="grid grid-cols-2 col-span-4 gap-4">
        <div>
          <InputLabel for="start" value="Start Date" />
          <TextInput
            id="start"
            v-model="form.start"
            type="datetime-local"
            class="block w-full mt-1"
          />
          <InputError :message="form.errors.start" class="mt-2" />
        </div>
        <div>
          <InputLabel for="end" value="End Date" />
          <TextInput
            id="end"
            v-model="form.end"
            type="datetime-local"
            class="block w-full mt-1"
          />
          <InputError :message="form.errors.end" class="mt-2" />
        </div>
      </div>

      <!-- Status Select Box -->
      <div class="col-span-6 sm:col-span-2">
        <InputLabel for="status" value="Status" />
        <select
          id="status"
          v-model="form.status"
          class="block w-full mt-1 border-gray-300 rounded-md shadow-sm"
        >
          <option value="" disabled>Select a status</option>
          <option value="0">Inactive</option>
          <option value="1">Active</option>
        </select>
        <InputError :message="form.errors.status" class="mt-2" />
      </div>

      <!-- Checkbox Grid -->
      <div class="grid grid-cols-2 col-span-6 gap-4 mt-4">
        <!-- Reporting Checkbox -->
        <div class="flex items-center">
          <input
            id="reporting"
            type="checkbox"
            v-model="form.reporting"
            class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
          />
          <label
            for="reporting"
            class="block ml-2 text-sm font-medium text-gray-700"
          >
            Reporting
          </label>
          <InputError :message="form.errors.reporting" class="mt-2" />
        </div>

        <!-- Force Lowercase Checkbox -->
        <div class="flex items-center">
          <input
            id="force_lowercase"
            type="checkbox"
            v-model="form.force_lowercase"
            class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
          />
          <label
            for="force_lowercase"
            class="block ml-2 text-sm font-medium text-gray-700"
          >
            Force Lowercase
          </label>
          <InputError :message="form.errors.force_lowercase" class="mt-2" />
        </div>

        <!-- UTM Activated Checkbox -->
        <div class="flex items-center">
          <input
            id="utm_activated"
            type="checkbox"
            v-model="form.utm_activated"
            class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
          />
          <label
            for="utm_activated"
            class="block ml-2 text-sm font-medium text-gray-700"
          >
            UTM Activated
          </label>
          <InputError :message="form.errors.utm_activated" class="mt-2" />
        </div>

        <!-- Monitor URLs Checkbox -->
        <div class="flex items-center">
          <input
            id="monitor_urls"
            type="checkbox"
            v-model="form.monitor_urls"
            class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
          />
          <label
            for="monitor_urls"
            class="block ml-2 text-sm font-medium text-gray-700"
          >
            Monitor URLs
          </label>
          <InputError :message="form.errors.monitor_urls" class="mt-2" />
        </div>
      </div>

      <!-- Description -->
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
        Update Campaign
      </PrimaryButton>
    </template>
  </FormSection>
</template>
