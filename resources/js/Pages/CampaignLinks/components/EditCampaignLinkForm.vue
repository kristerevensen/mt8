<script setup>
import { useForm } from "@inertiajs/vue3";
import FormSection from "@/Components/FormSection.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { defineProps } from "vue";

const props = defineProps({
  link: Object,
  campaigns: Array,
});

const form = useForm({
  link_name: props.link.link_name || "", // Link name
  url: props.link.url || "", // URL
  campaign_id: props.link.campaign_id || "", // Campaign ID
  description: props.link.description || "", // Description
});

const submitForm = () => {
  form.put(route("campaign-links.update", props.link.id), {
    onSuccess: () => alert("Link updated successfully!"),
    onError: () => alert("There was an error updating the link"),
  });
};
</script>

<template>
  <FormSection @submitted="submitForm()">
    <template #title> Edit Campaign Link Details </template>

    <template #description>
      Edit the link associated with a campaign.
    </template>

    <template #form>
      <div class="col-span-6">
        <InputLabel for="link_name" value="Link Name" />
        <TextInput
          id="link_name"
          v-model="form.link_name"
          type="text"
          class="block w-full mt-1"
          autofocus
        />
        <InputError :message="form.errors.link_name" class="mt-2" />
      </div>

      <div class="col-span-6">
        <InputLabel for="url" value="URL" />
        <TextInput
          id="url"
          v-model="form.url"
          type="url"
          class="block w-full mt-1"
        />
        <InputError :message="form.errors.url" class="mt-2" />
      </div>

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
