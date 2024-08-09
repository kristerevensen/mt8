<script setup>
import { useForm } from "@inertiajs/vue3";
import FormSection from "@/Components/FormSection.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";

// Define the props to receive campaigns and the existing link data
const props = defineProps({
  campaigns: Array, // List of campaigns to choose from
  link: Object, // Existing campaign link data
});

// Set up the form using Inertia's useForm hook, pre-filling with existing link data
const form = useForm({
  landing_page: props.link.landing_page || "", // Landing Page URL
  source: props.link.source ? props.link.source.toLowerCase() : "", // Source for UTM tracking
  medium: props.link.medium ? props.link.medium.toLowerCase() : "", // Medium for UTM tracking
  term: props.link.term ? props.link.term.toLowerCase() : "", // Term for UTM tracking
  content: props.link.content ? props.link.content.toLowerCase() : "", // Content for UTM tracking
  custom_parameters: props.link.custom_parameters || "", // Custom parameters for tracking
  description: props.link.description || "", // Description of the link
  campaign_id: props.link.campaign_id || "", // Campaign ID
});

console.log(form.medium);

// Method to submit the form data for updating
const submitForm = () => {
  form.put(route("campaign-links.update", props.link.id), {
    onSuccess: () => form.reset(),
  });
};
</script>

<template>
  <FormSection @submitted="submitForm()">
    <template #title>Edit Campaign Link Details</template>

    <template #description>
      Update the details of this campaign link.
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
        <select
          id="medium"
          v-model="form.medium"
          class="block w-full mt-1 border-gray-300 rounded-md shadow-sm"
        >
          <option value="" disabled>Select a medium</option>
          <option value="cpc">cpc</option>
          <option value="email">email</option>
          <option value="organic">organic</option>
          <option value="social">social</option>
          <option value="referral">referral</option>
          <option value="display">display</option>
          <option value="video">video</option>
          <option value="banner">banner</option>
          <option value="sms">sms</option>
          <option value="qr">qr</option>
          <option value="content">content</option>
          <option value="audio">audio</option>
          <option value="app">app</option>
          <option value="print">print</option>
          <option value="podcast">podcast</option>
          <option value="partner">partner</option>
          <option value="tv">tv</option>
          <option value="sponsored">sponsored</option>
        </select>
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
        Update Link
      </PrimaryButton>
    </template>
  </FormSection>
</template>
