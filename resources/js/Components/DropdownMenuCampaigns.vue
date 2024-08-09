<template>
  <Menu as="div" class="relative inline-block text-left">
    <div>
      <MenuButton
        class="inline-flex w-full justify-center gap-x-1.5 rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
      >
        Options
        <ChevronDownIcon
          class="w-5 h-5 -mr-1 text-gray-400"
          aria-hidden="true"
        />
      </MenuButton>
    </div>

    <transition
      enter-active-class="transition duration-100 ease-out"
      enter-from-class="transform scale-95 opacity-0"
      enter-to-class="transform scale-100 opacity-100"
      leave-active-class="transition duration-75 ease-in"
      leave-from-class="transform scale-100 opacity-100"
      leave-to-class="transform scale-95 opacity-0"
    >
      <MenuItems
        class="absolute right-0 z-10 w-56 mt-2 origin-top-right bg-white divide-y divide-gray-100 rounded-md shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
      >
        <div class="py-1">
          <MenuItem v-slot="{ active }">
            <a
              :href="newUrl"
              :class="[
                active ? 'bg-gray-100 text-gray-900' : 'text-gray-700',
                'group flex items-center px-4 py-2 text-sm',
              ]"
            >
              <DocumentDuplicateIcon
                class="w-5 h-5 mr-3 text-gray-400 group-hover:text-gray-500"
                aria-hidden="true"
              />
              New
            </a>
          </MenuItem>
          <MenuItem v-slot="{ active }">
            <a
              :href="editUrl"
              :class="[
                active ? 'bg-gray-100 text-gray-900' : 'text-gray-700',
                'group flex items-center px-4 py-2 text-sm',
              ]"
            >
              <PencilSquareIcon
                class="w-5 h-5 mr-3 text-gray-400 group-hover:text-gray-500"
                aria-hidden="true"
              />
              Edit
            </a>
          </MenuItem>
        </div>

        <div class="py-1">
          <MenuItem v-slot="{ active }">
            <button
              @click="deleteItem"
              :class="[
                active ? 'bg-gray-100 text-gray-900' : 'text-gray-700',
                'group flex items-center px-4 py-2 text-sm',
              ]"
            >
              <TrashIcon
                class="w-5 h-5 mr-3 text-gray-400 group-hover:text-gray-500"
                aria-hidden="true"
              />
              Delete
            </button>
          </MenuItem>
        </div>
      </MenuItems>
    </transition>
  </Menu>
</template>

<script setup>
import { Menu, MenuButton, MenuItem, MenuItems } from "@headlessui/vue";
import {
  ArchiveBoxIcon,
  ArrowRightCircleIcon,
  ChevronDownIcon,
  DocumentDuplicateIcon,
  HeartIcon,
  PencilSquareIcon,
  TrashIcon,
  UserPlusIcon,
} from "@heroicons/vue/20/solid";
import { defineProps, defineEmits } from "vue";

const props = defineProps({
  editUrl: String,
  duplicateUrl: String,
  newUrl: String,
});

const emit = defineEmits(["delete"]);

const deleteItem = () => {
  emit("delete");
};
</script>
