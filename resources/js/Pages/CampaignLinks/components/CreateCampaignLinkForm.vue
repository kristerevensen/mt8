<script setup>
import { useForm } from "@inertiajs/vue3";
import FormSection from "@/Components/FormSection.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";

// Define the props to receive campaigns from the parent component
const props = defineProps({
  campaigns: Array,
  campaign_token: String,
  campaign_id: Number,
});

// Set up the form using Inertia's useForm hook
const form = useForm({
  landing_page: "", // Landing Page URL
  source: "", // Source for UTM tracking
  medium: "", // Medium for UTM tracking
  term: "", // Term for UTM tracking
  content: "", // Content for UTM tracking
  custom_parameters: "", // Custom parameters for tracking
  description: "", // Description of the link
  campaign_token: props.campaigns.campaign_token || "", // Campaign ID
  campaign_id: props.campaign_id, // Campaign ID
});

// Method to submit the form data
const submitForm = () => {
  form.post(route("campaign-links.store"), {
    onSuccess: () => form.reset(), // Reset the form on success
    onError: () => alert("There was an error creating the link"), // Alert on error
  });
};
</script>

<template>
  <FormSection @submitted="submitForm()">
    <template #title>Campaign Link Details</template>

    <template #description>
      Create a new link associated with a campaign.
    </template>

    <template #form>
      <!-- Landing Page -->
      <div class="col-span-6">
        <InputLabel for="landing_page" value="Landing Page" />
        <TextInput
          id="landing_page"
          v-model="form.landing_page"
          type="url"
          class="block w-full mt-1"
        />
        <InputError :message="form.errors.landing_page" class="mt-2" />
      </div>

      <!-- Campaign Selection -->
      <div class="col-span-6">
        <InputLabel for="campaign_id" value="Campaign" />
        <select
          id="campaign_id"
          v-model="form.campaign_id"
          class="block w-full mt-1 border-gray-300 rounded-md shadow-sm"
        >
          <option value="" disabled>Select a campaign</option>
          <option
            v-for="campaign in campaigns"
            :key="campaign.id"
            :value="campaign.id"
          >
            {{ campaign.campaign_name }}
          </option>
        </select>
        <InputError :message="form.errors.campaign_id" class="mt-2" />
      </div>

      <!-- Source for UTM tracking -->
      <div class="col-span-3">
        <InputLabel for="source" value="Source" />
        <TextInput
          id="source"
          v-model="form.source"
          type="text"
          class="block w-full mt-1"
        />
        <InputError :message="form.errors.source" class="mt-2" />
      </div>

      <!-- Medium for UTM tracking -->
      <div class="col-span-3">
        <InputLabel for="medium" value="Medium" />
        <TextInput
          id="medium"
          v-model="form.medium"
          type="text"
          class="block w-full mt-1"
        />
        <InputError :message="form.errors.medium" class="mt-2" />
      </div>

      <!-- Term for UTM tracking -->
      <div class="col-span-3">
        <InputLabel for="term" value="Term" />
        <TextInput
          id="term"
          v-model="form.term"
          type="text"
          class="block w-full mt-1"
        />
        <InputError :message="form.errors.term" class="mt-2" />
      </div>

      <!-- Content for UTM tracking -->
      <div class="col-span-3">
        <InputLabel for="content" value="Content" />
        <TextInput
          id="content"
          v-model="form.content"
          type="text"
          class="block w-full mt-1"
        />
        <InputError :message="form.errors.content" class="mt-2" />
      </div>

      <!-- Custom Parameters -->
      <div class="col-span-6">
        <InputLabel for="custom_parameters" value="Custom Parameters" />
        <textarea
          id="custom_parameters"
          v-model="form.custom_parameters"
          class="block w-full mt-1 border-gray-300 rounded-md shadow-sm"
          rows="3"
        ></textarea>
        <InputError :message="form.errors.custom_parameters" class="mt-2" />
      </div>

      <!-- Description -->
      <div class="col-span-6">
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
        Create Link
      </PrimaryButton>
    </template>
  </FormSection>
</template>
