<script setup>
import { useForm, Link } from "@inertiajs/vue3";
import FormSection from "@/Components/FormSection.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { ref } from "vue";

// Define props to receive existing project data and other necessary data
const props = defineProps({
  project: Object, // Existing project data
  teams: Array, // Available teams
  locations: Array, // Available locations
  languages: Array, // Available languages
});

// Initialize form data with existing project details
const form = useForm({
  project_name: props.project.project_name || "",
  project_domain: props.project.project_domain || "",
  project_language: props.project.project_language || "",
  project_location_code: props.project.location_code || "",
  project_category: props.project.project_category || [],
  team_id: props.project.team_id || "",
});

const categories = ref([
  {
    name: "Agency",
    description:
      "For agencies and consultancy firms offering professional services.",
  },
  {
    name: "Brand",
    description: "For corporate and brand representation websites.",
  },
  {
    name: "Blog",
    description:
      "For personal or professional blogs to share articles and posts.",
  },
  {
    name: "Content",
    description:
      "For content-heavy sites, including news portals and magazines.",
  },
  {
    name: "Commercial",
    description: "For commercial purposes such as product promotion and sales.",
  },
  {
    name: "Portfolio",
    description: "To showcase personal or company work and projects.",
  },
  {
    name: "Personal",
    description: "For personal websites, resumes, and online profiles.",
  },
  {
    name: "Non-profit",
    description: "For charitable organizations and non-profit entities.",
  },
  {
    name: "Educational",
    description: "For educational institutions and e-learning platforms.",
  },
  {
    name: "Government",
    description: "For government agencies and public sector entities.",
  },
  {
    name: "Healthcare",
    description: "For healthcare providers and medical services.",
  },
  {
    name: "Entertainment",
    description:
      "For entertainment purposes, including movies, music, and games.",
  },
  {
    name: "Technology",
    description: "For tech companies, innovations, and technology news.",
  },
  {
    name: "Travel",
    description: "For travel agencies, guides, and tourism destinations.",
  },
  {
    name: "Finance",
    description: "For financial services, banking, and investment advisory.",
  },
  {
    name: "Social Media",
    description: "For social networking platforms and online communication.",
  },
  {
    name: "News",
    description: "For news websites and news aggregators providing updates.",
  },
  {
    name: "Real Estate",
    description: "For real estate businesses and property listings.",
  },
  {
    name: "Community",
    description: "For forums, discussion groups, and online communities.",
  },
  {
    name: "Other",
    description: "For categories not covered by the above classifications.",
  },
]);

categories.value.sort((a, b) => a.name.localeCompare(b.name));

// Function to handle form submission
const submitForm = () => {
  console.log(form);
  form.put(route("projects.update", props.project.project_code), {
    onSuccess: () => {
      console.log("Project updated successfully.");
    },
    onError: () => {
      alert("There was an error updating the project.");
    },
  });
};
</script>

<template>
  <FormSection @submitted="submitForm()">
    <template #title> Project Details </template>

    <template #description>
      Edit the project details. Please make any necessary changes and submit the
      form to update the project.
    </template>

    <template #form>
      <!-- Project Owner -->
      <div class="col-span-6">
        <InputLabel value="Project Owner" />
        <div class="flex items-center mt-2">
          <img
            class="object-cover w-12 h-12 rounded-full"
            :src="$page.props.auth.user.profile_photo_url"
            :alt="$page.props.auth.user.name"
          />
          <div class="leading-tight ms-4">
            <div class="text-gray-900">{{ $page.props.auth.user.name }}</div>
            <div class="text-sm text-gray-700">
              {{ $page.props.auth.user.email }}
            </div>
          </div>
        </div>
      </div>

      <!-- Project Name -->
      <div class="col-span-6 sm:col-span-4">
        <InputLabel for="project_name" value="Project Name" />
        <TextInput
          id="project_name"
          v-model="form.project_name"
          type="text"
          class="block w-full mt-1"
          autofocus
        />
        <InputError :message="form.errors.project_name" class="mt-2" />
      </div>

      <!-- Project Domain -->
      <div class="col-span-6 sm:col-span-4">
        <InputLabel for="project_domain" value="Project Domain" />
        <TextInput
          id="project_domain"
          v-model="form.project_domain"
          type="text"
          class="block w-full mt-1"
        />
        <InputError :message="form.errors.project_domain" class="mt-2" />
      </div>

      <!-- Teams Dropdown -->
      <div class="col-span-6 sm:col-span-4">
        <InputLabel for="project_team" value="Project Team" />
        <!-- Check if the teams array is empty -->
        <template v-if="$page.props.teams.length > 0">
          <select
            id="project_team"
            v-model="form.team_id"
            class="block w-full mt-1 border-gray-300 rounded-md shadow-sm"
          >
            <option value="" disabled>Select a Team</option>
            <option
              v-for="team in $page.props.teams"
              :key="team.id"
              :value="team.id"
            >
              {{ team.name }}
            </option>
          </select>
          <InputError :message="form.errors.team_id" class="mt-2" />
        </template>
        <!-- Show a link to create a new team if no teams exist -->
        <template v-else>
          <p class="mt-2 text-sm text-gray-500">
            No teams available. Please
            <Link
              :href="route('teams.create')"
              class="text-indigo-600 underline"
            >
              create a team
            </Link>
            first.
          </p>
        </template>
      </div>

      <!-- Location Dropdown -->
      <div class="col-span-6 sm:col-span-4">
        <InputLabel for="project_location" value="Project Country" />
        <select
          id="project_location"
          v-model="form.project_location_code"
          class="block w-full mt-1 border-gray-300 rounded-md shadow-sm"
        >
          <option value="" disabled>Select a Country</option>
          <option
            v-for="location in $page.props.locations"
            :key="location.location_code"
            :value="location.location_code"
          >
            {{ location.location_name }}
          </option>
        </select>
        <InputError :message="form.errors.location_code" class="mt-2" />
      </div>

      <!-- Language Dropdown -->
      <div class="col-span-6 sm:col-span-4">
        <InputLabel for="project_language" value="Project Language" />
        <select
          id="project_language"
          v-model="form.project_language"
          class="block w-full mt-1 border-gray-300 rounded-md shadow-sm"
        >
          <option value="" disabled>Select a language</option>
          <option
            v-for="language in $page.props.languages"
            :key="language.language_code"
            :value="language.language_code"
          >
            {{ language.language_name }}
          </option>
        </select>
        <InputError :message="form.errors.project_language" class="mt-2" />
      </div>

      <!-- Categories Checkboxes -->
      <div class="col-span-6 sm:col-span-4">
        <InputLabel for="project_category" value="Project Categories" />
        <span class="text-sm text-gray-500">
          Select multiple categories as needed.
        </span>
        <InputError :message="form.errors.project_category" class="mt-2" />
        <div class="mt-2 space-y-2">
          <div
            v-for="category in categories"
            :key="category.name"
            class="flex items-start"
          >
            <div class="flex items-center h-5">
              <input
                id="category-{{ category.name }}"
                type="checkbox"
                v-model="form.project_category"
                :value="category.name"
                class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
              />
            </div>
            <div class="ml-3 text-sm">
              <label
                for="category-{{ category.name }}"
                class="font-medium text-gray-700"
              >
                {{ category.name }}
              </label>
              <p class="text-gray-500">{{ category.description }}</p>
            </div>
          </div>
        </div>
        <InputError :message="form.errors.project_category" class="mt-2" />
      </div>
    </template>

    <template #actions>
      <PrimaryButton
        :class="{ 'opacity-25': form.processing }"
        :disabled="form.processing"
      >
        Update
      </PrimaryButton>
    </template>
  </FormSection>
</template>
