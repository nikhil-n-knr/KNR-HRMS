<template>
    <div class="h-full flex flex-col bg-gray-50">
        <div class="h-16 border-b border-gray-200 bg-white flex items-center justify-between px-8 shrink-0 shadow-sm">
            <div>
                <h2 class="text-xl font-black text-gray-900 tracking-tight">Security</h2>
                <p class="text-xs text-gray-500 mt-0.5">Monitor threats, manage firewall rules, and harden your CMS.</p>
            </div>
            <button @click="runScan" :disabled="scanning" class="px-4 py-2 bg-red-600 text-white rounded-xl text-sm font-bold shadow-md shadow-red-500/20 hover:bg-red-700 transition-all flex items-center gap-2 disabled:opacity-50">
                <i v-if="scanning" class="fas fa-spinner fa-spin text-xs"></i>
                <i v-else class="fas fa-shield-alt text-xs"></i>
                Run Security Scan
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-8 space-y-6">
            <!-- Security Score + KPIs -->
            <div class="grid grid-cols-5 gap-4">
                <!-- Score -->
                <div class="col-span-1 bg-white rounded-2xl border border-gray-200 p-5 shadow-sm flex flex-col items-center justify-center">
                    <div class="relative w-24 h-24 mb-3">
                        <svg class="w-24 h-24 -rotate-90" viewBox="0 0 100 100">
                            <circle cx="50" cy="50" r="40" fill="none" stroke="#fef2f2" stroke-width="12"/>
                            <circle cx="50" cy="50" r="40" fill="none" :stroke="secScore >= 80 ? '#10b981' : secScore >= 60 ? '#f59e0b' : '#ef4444'" stroke-width="12"
                                :stroke-dasharray="`${secScore * 2.51} 251`" stroke-linecap="round" style="transition:stroke-dasharray 1.2s ease;"/>
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="text-2xl font-black" :style="{color: secScore >= 80 ? '#10b981' : secScore >= 60 ? '#f59e0b' : '#ef4444'}">{{ secScore }}</span>
                        </div>
                    </div>
                    <p class="text-xs font-black text-gray-700">Security Score</p>
                    <span class="mt-1 text-sm font-black px-2 py-0.5 rounded-full" :class="secScore >= 80 ? 'bg-emerald-100 text-emerald-700' : secScore >= 60 ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700'">
                        {{ secScore >= 80 ? 'Good' : secScore >= 60 ? 'Fair' : 'At Risk' }}
                    </span>
                </div>
                <!-- KPIs -->
                <div v-for="k in kpis" :key="k.label" class="bg-white rounded-2xl border border-gray-200 p-4 shadow-sm flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0" :style="{background: k.bg, color: k.color}">
                        <i :class="k.icon" class="text-sm"></i>
                    </div>
                    <div>
                        <p class="text-xl font-black text-gray-900">{{ k.value }}</p>
                        <p class="text-sm font-bold text-gray-400 uppercase tracking-wider leading-tight">{{ k.label }}</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-5">
                <!-- SSL Certificate -->
                <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm">
                    <h3 class="font-black text-gray-900 text-sm flex items-center gap-2 mb-4">
                        <i class="fas fa-lock text-emerald-500"></i> SSL Certificate
                    </h3>
                    <div class="space-y-3">
                        <div v-for="row in sslInfo" :key="row.label" class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
                            <span class="text-xs text-gray-500 font-medium">{{ row.label }}</span>
                            <span class="text-xs font-black" :class="row.ok ? 'text-emerald-600' : 'text-red-600'">{{ row.value }}</span>
                        </div>
                        <div class="mt-3 p-3 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center gap-2">
                            <i class="fas fa-check-circle text-emerald-500"></i>
                            <span class="text-xs font-bold text-emerald-700">SSL is valid — expires in {{ sslDays }} days</span>
                        </div>
                    </div>
                </div>

                <!-- Two-Factor Auth -->
                <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm">
                    <h3 class="font-black text-gray-900 text-sm flex items-center gap-2 mb-4">
                        <i class="fas fa-mobile-alt text-indigo-500"></i> Two-Factor Authentication
                    </h3>
                    <div class="space-y-3">
                        <label class="flex items-center gap-3 cursor-pointer p-3 rounded-xl hover:bg-gray-50">
                            <div class="relative">
                                <input type="checkbox" v-model="twoFA.admin" class="sr-only peer" />
                                <div class="w-10 h-5 bg-gray-200 peer-checked:bg-indigo-500 rounded-full transition-colors relative after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:w-4 after:h-4 after:bg-white after:rounded-full after:transition-all peer-checked:after:translate-x-5 after:shadow-sm"></div>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-800">Require 2FA for Admins</p>
                                <p class="text-sm text-gray-400">All users with admin role must have 2FA enabled.</p>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer p-3 rounded-xl hover:bg-gray-50">
                            <div class="relative">
                                <input type="checkbox" v-model="twoFA.all" class="sr-only peer" />
                                <div class="w-10 h-5 bg-gray-200 peer-checked:bg-indigo-500 rounded-full transition-colors relative after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:w-4 after:h-4 after:bg-white after:rounded-full after:transition-all peer-checked:after:translate-x-5 after:shadow-sm"></div>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-800">Require 2FA for All Users</p>
                                <p class="text-sm text-gray-400">Every logged-in user must set up an authenticator.</p>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer p-3 rounded-xl hover:bg-gray-50">
                            <div class="relative">
                                <input type="checkbox" v-model="twoFA.loginAlert" class="sr-only peer" />
                                <div class="w-10 h-5 bg-gray-200 peer-checked:bg-indigo-500 rounded-full transition-colors relative after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:w-4 after:h-4 after:bg-white after:rounded-full after:transition-all peer-checked:after:translate-x-5 after:shadow-sm"></div>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-800">New Device Login Alert</p>
                                <p class="text-sm text-gray-400">Send email alert on login from new device/IP.</p>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Login Attempts -->
            <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-black text-gray-900 text-sm flex items-center gap-2">
                            <i class="fas fa-user-lock text-amber-500"></i> Recent Login Attempts
                        </h3>
                        <div class="flex items-center gap-2">
                             <input v-model="logSearch" placeholder="Search email/IP..." class="bg-gray-50 border border-gray-100 rounded-lg px-3 py-1 text-base focus:outline-none focus:ring-1 focus:ring-indigo-400" />
                             <button @click="blockAll" class="px-3 py-1.5 text-sm font-black bg-red-50 text-red-600 border border-red-200 rounded-lg hover:bg-red-100">Block All Failed</button>
                        </div>
                    </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead>
                            <tr class="border-b border-gray-100">
                                <th class="text-left py-2 px-3 text-sm font-black uppercase tracking-wider text-gray-400">Time</th>
                                <th class="text-left py-2 px-3 text-sm font-black uppercase tracking-wider text-gray-400">User / Email</th>
                                <th class="text-left py-2 px-3 text-sm font-black uppercase tracking-wider text-gray-400">IP Address</th>
                                <th class="text-left py-2 px-3 text-sm font-black uppercase tracking-wider text-gray-400">Status</th>
                                <th class="text-left py-2 px-3 text-sm font-black uppercase tracking-wider text-gray-400">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr v-for="log in loginLogs" :key="log.id" class="hover:bg-gray-50 transition-colors">
                                <td class="py-2.5 px-3 text-gray-500 font-mono text-sm">{{ log.time }}</td>
                                <td class="py-2.5 px-3 font-bold text-gray-800">{{ log.email }}</td>
                                <td class="py-2.5 px-3 font-mono text-gray-500">{{ log.ip }}</td>
                                <td class="py-2.5 px-3">
                                    <span class="px-2 py-0.5 rounded-full text-sm font-black"
                                        :class="log.status === 'success' ? 'bg-emerald-100 text-emerald-700' : log.status === 'failed' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700'">
                                        {{ log.status }}
                                    </span>
                                </td>
                                <td class="py-2.5 px-3">
                                    <button v-if="log.status !== 'success'" @click="blockIp(log.ip)"
                                        class="text-sm font-bold text-red-600 hover:text-red-800 flex items-center gap-1">
                                        <i class="fas fa-ban text-xs"></i> Block IP
                                    </button>
                                    <span v-else class="text-sm text-gray-300">—</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Blocked IPs + Firewall Rules -->
            <div class="grid grid-cols-2 gap-5">
                <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm">
                    <h3 class="font-black text-gray-900 text-sm flex items-center gap-2 mb-4">
                        <i class="fas fa-ban text-red-500"></i> Blocked IPs
                        <span class="text-sm font-black bg-red-100 text-red-700 px-2 py-0.5 rounded-full ml-1">{{ blockedIps.length }}</span>
                    </h3>
                    <div class="space-y-1.5 mb-3 max-h-40 overflow-y-auto">
                        <div v-for="ip in blockedIps" :key="ip" class="flex items-center justify-between px-3 py-2 bg-red-50 rounded-xl border border-red-100">
                            <span class="font-mono text-xs font-bold text-red-700">{{ ip }}</span>
                            <button @click="unblockIp(ip)" class="text-sm text-gray-400 hover:text-red-600 font-bold transition-colors">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div v-if="!blockedIps.length" class="text-center py-4 text-gray-400 text-xs">No IPs blocked</div>
                    </div>
                    <div class="flex gap-2 mt-2">
                        <input v-model="newIp" placeholder="Enter IP to block..." class="flex-1 bg-gray-50 border border-gray-200 rounded-lg px-3 py-1.5 text-xs font-mono focus:outline-none focus:border-red-400" />
                        <button @click="addBlock" class="px-3 py-1.5 bg-red-600 text-white rounded-lg text-xs font-bold hover:bg-red-700">Block</button>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm">
                    <h3 class="font-black text-gray-900 text-sm flex items-center gap-2 mb-4">
                        <i class="fas fa-fire text-orange-500"></i> Firewall Rules
                    </h3>
                    <div class="space-y-2">
                        <label v-for="rule in firewallRules" :key="rule.key" class="flex items-center gap-3 cursor-pointer group">
                            <div class="relative">
                                <input type="checkbox" v-model="rule.enabled" class="sr-only peer" />
                                <div class="w-9 h-4 bg-gray-200 peer-checked:bg-emerald-500 rounded-full transition-colors relative after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:w-3 after:h-3 after:bg-white after:rounded-full after:transition-all peer-checked:after:translate-x-5 after:shadow-sm"></div>
                            </div>
                            <div class="flex-1">
                                <p class="text-xs font-bold text-gray-800 group-hover:text-indigo-700 transition-colors">{{ rule.name }}</p>
                                <p class="text-sm text-gray-400">{{ rule.desc }}</p>
                            </div>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';

const scanning  = ref(false);
const secScore  = ref(74);
const sslDays   = ref(287);
const newIp     = ref('');

const twoFA = ref({ admin: true, all: false, loginAlert: true });

const kpis = [
    { label:'Blocked IPs',    value:3,   icon:'fas fa-ban',          color:'#ef4444', bg:'#fef2f2' },
    { label:'Failed Logins',  value:12,  icon:'fas fa-user-slash',   color:'#f59e0b', bg:'#fffbeb' },
    { label:'Threats Blocked',value:47,  icon:'fas fa-shield-alt',   color:'#10b981', bg:'#ecfdf5' },
    { label:'Active Sessions',value:3,   icon:'fas fa-desktop',      color:'#6366f1', bg:'#eef2ff' },
];

const sslInfo = [
    { label:'Status',     value:'Valid ✓',         ok:true  },
    { label:'Issuer',     value:"Let's Encrypt",   ok:true  },
    { label:'Protocol',   value:'TLS 1.3',          ok:true  },
    { label:'HSTS',       value:'Enabled',          ok:true  },
    { label:'Auto-renew', value:'On (via cron)',    ok:true  },
];

const loginLogs = ref([
    { id:1, time:'10:02 AM', email:'admin@site.com',     ip:'103.21.244.0', status:'success' },
    { id:2, time:'09:48 AM', email:'test@hacker.com',    ip:'45.33.32.156', status:'failed'  },
    { id:3, time:'09:42 AM', email:'test@hacker.com',    ip:'45.33.32.156', status:'failed'  },
    { id:4, time:'09:31 AM', email:'nikhil@company.com', ip:'49.205.75.10', status:'success' },
    { id:5, time:'08:55 AM', email:'bot@spam.ru',        ip:'185.220.101.3',status:'blocked' },
]);

const blockedIps = ref(['45.33.32.156', '185.220.101.3']);

const firewallRules = ref([
    { key:'rate_limit',    name:'Rate Limiting',           desc:'Max 100 requests/min per IP', enabled:true  },
    { key:'sql_inject',    name:'SQL Injection Protection', desc:'Block common SQLi patterns',  enabled:true  },
    { key:'xss',           name:'XSS Protection',          desc:'Strip script tags from inputs', enabled:true  },
    { key:'csrf',          name:'CSRF Tokens',             desc:'All POST forms require valid CSRF token', enabled:true  },
    { key:'uploads',       name:'File Upload Restrictions', desc:'Allow only images, PDFs, docs', enabled:true  },
    { key:'brute_force',   name:'Brute Force Protection',  desc:'Lock account after 5 failed logins', enabled:false },
]);

const blockIp  = (ip) => { if (!blockedIps.value.includes(ip)) blockedIps.value.push(ip); };
const unblockIp = (ip) => { blockedIps.value = blockedIps.value.filter(x => x !== ip); };
const addBlock  = () => { if (newIp.value.trim()) { blockIp(newIp.value.trim()); newIp.value = ''; } };
const blockAll  = () => {
    loginLogs.value.filter(l => l.status === 'failed' || l.status === 'blocked').forEach(l => blockIp(l.ip));
};
const runScan   = async () => {
    scanning.value = true;
    setTimeout(() => {
        secScore.value = Math.floor(Math.random() * 20) + 75;
        scanning.value = false;
    }, 2500);
};
</script>
