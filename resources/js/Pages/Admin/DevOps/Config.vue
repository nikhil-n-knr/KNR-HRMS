<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { CheckCircleIcon, ArrowRightIcon, CircleStackIcon, GlobeAltIcon, ServerIcon, CheckBadgeIcon } from '@heroicons/vue/24/outline';
import axios from 'axios';

const props = defineProps({
    providers: Array
});

// Navigation Tabs
const tabs = [
    { name: 'Overview', route: 'admin.devops.dashboard', current: false },
    { name: 'Providers & Configuration', route: 'admin.devops.providers.index', current: true },
];


// Wizard State
const currentStep = ref(1);
const activeProvider = ref(null); // 'github', 'bitbucket', 'gitlab', 'azure'

// Step 2 Form
const authForm = ref({ type: '', name: '', access_token: '', base_url: '' });
const isTestingConnection = ref(false);
const isConnectionVerified = ref(false);

// Step 3 Variables
const savedProvider = ref(null);
const remoteRepos = ref([]);
const projects = ref([]);
const systemModules = ref([]);
const selectedRepos = ref([]); // Store selected repo IDs
const mappings = ref({}); // { repoId: { mode: 'project', project_id: null, app_sub_module_id: null } }
const isLoadingRepos = ref(false);
const isConfiguringWebhooks = ref(false);

// Provider Options
const providerOptions = [
    { id: 'github', name: 'GitHub', icon: GlobeAltIcon, description: 'Connect via Personal Access Token' },
    { id: 'bitbucket', name: 'Bitbucket', icon: CircleStackIcon, description: 'Connect via App Passwords' },
    { id: 'gitlab', name: 'GitLab', icon: ServerIcon, description: 'Supports self-hosted instances' },
];

const selectProvider = (id) => {
    activeProvider.value = id;
    authForm.value.type = id;
    authForm.value.name = providerOptions.find(p => p.id === id).name;
    currentStep.value = 2;
};

const testConnection = async () => {
    isTestingConnection.value = true;
    isConnectionVerified.value = false;
    try {
        await axios.post(route('admin.devops.verify-token'), authForm.value);
        isConnectionVerified.value = true;
        alert('Connection successful!');
    } catch (e) {
        alert(e.response?.data?.message || 'Connection failed.');
    } finally {
        isTestingConnection.value = false;
    }
};

const saveAndNext = () => {
    if (!isConnectionVerified.value) {
        if (!confirm('Connection not verified. Save anyway?')) return;
    }
    
    router.post(route('admin.devops.providers.store'), authForm.value, {
        onSuccess: async (page) => {
            // Retrieve the newly created provider ID
            const newProvider = page.props.providers.find(p => p.name === authForm.value.name && p.type === authForm.value.type);
            if (newProvider) {
                savedProvider.value = newProvider;
                await loadRepositoriesAndOptions(newProvider);
                currentStep.value = 3;
            }
        },
        onError: (errors) => {
            alert('Failed to save provider: ' + JSON.stringify(errors));
        }
    });
};

const loadRepositoriesAndOptions = async (provider) => {
    isLoadingRepos.value = true;
    try {
        const [reposRes, projRes, modsRes] = await Promise.all([
             axios.get(route('admin.devops.providers.repos', provider.id)),
             axios.get(route('admin.devops.list.projects')),
             axios.get(route('admin.devops.list.modules')) 
        ]);
        
        remoteRepos.value = reposRes.data;
        projects.value = projRes.data;
        systemModules.value = modsRes.data;
        
        // Initialize mappings
        remoteRepos.value.forEach(repo => {
            mappings.value[repo.id] = { mode: 'project', project_id: '', app_sub_module_id: '' };
        });

    } catch (e) {
        console.error(e);
        alert('Failed to fetch data.');
    } finally {
        isLoadingRepos.value = false;
    }
};

const mapAndConfigureWebhooks = async () => {
    if (selectedRepos.value.length === 0) {
        alert('Please select at least one repository to proceed.');
        return;
    }
    
    isConfiguringWebhooks.value = true;
    let successCount = 0;

    for (const repoId of selectedRepos.value) {
        const repo = remoteRepos.value.find(r => r.id === repoId);
        const mapData = mappings.value[repoId];
        
        if (mapData.mode === 'project' && !mapData.project_id) continue;
        if (mapData.mode === 'app_module' && !mapData.app_sub_module_id) continue;

        try {
            // 1. Map Repo to DB
            const mapRes = await axios.post(route('admin.devops.repos.map'), {
                git_provider_id: savedProvider.value.id,
                external_id: repo.id,
                name: repo.name,
                url: repo.url,
                clone_url: repo.clone_url,
                project_id: mapData.mode === 'project' ? mapData.project_id : null,
                app_sub_module_id: mapData.mode === 'app_module' ? mapData.app_sub_module_id : null
            });
            
            // 2. Configure Webhooks
            const dbRepoId = mapRes.data.repository.id;
            await axios.post(route('admin.devops.repos.webhooks', dbRepoId));
            
            successCount++;
        } catch (e) {
            console.error(`Failed for repo ${repo.name}`, e);
        }
    }
    
    isConfiguringWebhooks.value = false;
    alert(`Successfully mapped and configured webhooks for ${successCount} repositories!`);
    router.visit(route('admin.devops.providers.index'));
};

const skipToDashboard = () => {
    router.visit(route('admin.devops.dashboard'));
};
</script>

<template>
    <Head title="Git Integration Setup" />

    <MainLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Git Integration Wizard</h2>
                <button v-if="providers.length > 0" @click="skipToDashboard" class="text-sm text-indigo-600 hover:text-indigo-900 underline">
                    Skip to Dashboard
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
                
                <!-- Tab Navigation -->
                <div class="border-b border-gray-200 mb-6">
                    <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                        <Link v-for="tab in tabs" :key="tab.name" :href="route(tab.route)"
                             :class="[tab.current ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300', 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm']"
                             :aria-current="tab.current ? 'page' : undefined">
                            {{ tab.name }}
                        </Link>
                    </nav>
                </div>

                <!-- Stepper -->
                <nav aria-label="Progress" class="mb-8">
                    <ol role="list" class="flex items-center">
                        <li class="relative pr-8 sm:pr-20">
                            <div class="absolute inset-0 flex items-center" aria-hidden="true">
                                <div :class="[currentStep > 1 ? 'bg-indigo-600' : 'bg-gray-200', 'h-0.5 w-full']"></div>
                            </div>
                            <button @click="currentStep = 1" class="relative flex h-8 w-8 items-center justify-center rounded-full bg-white ring-2 ring-indigo-600">
                                <CheckCircleIcon v-if="currentStep > 1" class="h-5 w-5 text-indigo-600" aria-hidden="true" />
                                <span v-else class="text-indigo-600 text-sm font-medium">1</span>
                            </button>
                        </li>
                        <li class="relative pr-8 sm:pr-20">
                            <div class="absolute inset-0 flex items-center" aria-hidden="true">
                                <div :class="[currentStep > 2 ? 'bg-indigo-600' : 'bg-gray-200', 'h-0.5 w-full']"></div>
                            </div>
                            <button @click="currentStep > 1 ? currentStep = 2 : null" :class="[currentStep >= 2 ? 'ring-indigo-600 bg-white' : 'bg-gray-100 ring-gray-300', 'relative flex h-8 w-8 items-center justify-center rounded-full ring-2']">
                                <CheckCircleIcon v-if="currentStep > 2" class="h-5 w-5 text-indigo-600" aria-hidden="true" />
                                <span v-else :class="[currentStep >= 2 ? 'text-indigo-600' : 'text-gray-500', 'text-sm font-medium']">2</span>
                            </button>
                        </li>
                        <li class="relative">
                            <button :class="[currentStep >= 3 ? 'ring-indigo-600 bg-white' : 'bg-gray-100 ring-gray-300', 'relative flex h-8 w-8 items-center justify-center rounded-full ring-2']">
                                <span :class="[currentStep >= 3 ? 'text-indigo-600' : 'text-gray-500', 'text-sm font-medium']">3</span>
                            </button>
                        </li>
                    </ol>
                    <div class="flex text-xs font-medium text-gray-500 mt-2 space-x-[4rem] sm:space-x-[9rem]">
                        <span>Select Provider</span>
                        <span>Authentication</span>
                        <span>Discovery & Mapping</span>
                    </div>
                </nav>

                <!-- Step 1: Provider Selection -->
                <div v-show="currentStep === 1" class="bg-white shadow rounded-lg p-8">
                    <h3 class="text-lg font-bold text-gray-900 mb-6">Step 1: Choose Your Git Provider</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div v-for="provider in providerOptions" :key="provider.id"
                             @click="selectProvider(provider.id)"
                             class="border-2 border-gray-200 rounded-lg p-6 flex flex-col items-center text-center cursor-pointer hover:border-indigo-500 hover:bg-indigo-50 transition-colors">
                            <component :is="provider.icon" class="h-12 w-12 text-gray-600 mb-4" />
                            <h4 class="text-md font-semibold text-gray-900">{{ provider.name }}</h4>
                            <p class="text-sm text-gray-500 mt-2">{{ provider.description }}</p>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Authentication -->
                <div v-show="currentStep === 2" class="bg-white shadow rounded-lg p-8">
                    <h3 class="text-lg font-bold text-gray-900 mb-6">Step 2: Connect to {{ authForm.name }}</h3>
                    <div class="max-w-xl mx-auto space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Display Name</label>
                            <input v-model="authForm.name" type="text" placeholder="e.g. Acme Backend Repository" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Personal Access Token</label>
                            <input v-model="authForm.access_token" type="password" placeholder="ghp_******" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <p class="text-xs text-gray-500 mt-1">Requires 'repo' scope for webhooks and code access.</p>
                        </div>
                        <div v-if="['gitlab', 'azure'].includes(activeProvider)">
                            <label class="block text-sm font-medium text-gray-700">Base URL (Self-hosted)</label>
                            <input v-model="authForm.base_url" type="url" placeholder="https://gitlab.mycompany.com" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>

                        <div class="flex items-center space-x-4 pt-4 border-t border-gray-200">
                            <button @click="currentStep = 1" class="text-gray-600 hover:text-gray-900 font-medium text-sm">
                                Back
                            </button>
                            <button @click="testConnection" :disabled="isTestingConnection" class="btn-secondary px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 flex items-center">
                                <span v-if="isTestingConnection">Testing...</span>
                                <span v-else>Test Connection</span>
                            </button>
                            <button @click="saveAndNext" :class="[isConnectionVerified ? 'bg-indigo-600 hover:bg-indigo-700' : 'bg-gray-400', 'px-4 py-2 rounded-md shadow-sm text-sm font-medium text-white flex items-center']">
                                <CheckBadgeIcon v-if="isConnectionVerified" class="w-5 h-5 mr-1" />
                                Save & Next <ArrowRightIcon class="w-4 h-4 ml-2" />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Discovery & Mapping -->
                <div v-show="currentStep === 3" class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Step 3: Discovery & Automated Mapping</h3>
                    <p class="text-sm text-gray-500 mb-6">Select the repositories you want to track and link them to your existing OPSCORE projects. We will automatically configure webhooks to import PRs and commits.</p>
                    
                    <div v-if="isLoadingRepos" class="py-12 text-center text-gray-500 animate-pulse">
                        Discovering repositories...
                    </div>
                    
                    <div v-else class="overflow-hidden border border-gray-200 sm:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-10">Track</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Repository Name</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Link To Project / Module</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="repo in remoteRepos" :key="repo.id" :class="{'bg-indigo-50': selectedRepos.includes(repo.id)}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox" :value="repo.id" v-model="selectedRepos" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap font-medium text-sm text-gray-900">
                                        {{ repo.name }}
                                        <span v-if="repo.private" class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">Private</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <!-- Only show mapping controls if tracked -->
                                        <div v-if="selectedRepos.includes(repo.id)" class="flex space-x-2 items-center">
                                            <select v-model="mappings[repo.id].mode" class="block w-32 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                                <option value="project">Project</option>
                                                <option value="app_module">System Module</option>
                                            </select>
                                            
                                            <select v-if="mappings[repo.id].mode === 'project'" v-model="mappings[repo.id].project_id" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                                <option value="">-- Choose Project --</option>
                                                <option v-for="proj in projects" :key="proj.id" :value="proj.id">{{ proj.name }}</option>
                                            </select>
                                            
                                            <select v-if="mappings[repo.id].mode === 'app_module'" v-model="mappings[repo.id].app_sub_module_id" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                                <option value="">-- Choose Module --</option>
                                                <option v-for="mod in systemModules" :key="mod.id" :value="mod.id">{{ mod.name }}</option>
                                            </select>
                                        </div>
                                        <div v-else class="text-gray-400 italic text-xs">Select to map</div>
                                    </td>
                                </tr>
                                <tr v-if="remoteRepos.length === 0">
                                    <td colspan="3" class="px-6 py-4 text-center text-gray-500">No repositories found or token lacks permissions.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-6 flex justify-end">
                        <button @click="mapAndConfigureWebhooks" :disabled="isConfiguringWebhooks || selectedRepos.length === 0" class="btn-primary flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50">
                            <span v-if="isConfiguringWebhooks">Configuring Webhooks...</span>
                            <span v-else>Configure Webhooks & Finish</span>
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </MainLayout>
</template>
