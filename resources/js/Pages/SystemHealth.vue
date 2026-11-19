<script setup>
import { computed } from 'vue';

const props = defineProps({
    health: {
        type: Object,
        required: true
    }
});

const timestamp = computed(() => {
    return new Date(props.health.timestamp).toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
});

const latencyBadgeClass = computed(() => {
    return props.health.database.connected 
        ? 'bg-emerald-100 text-emerald-800' 
        : 'bg-red-100 text-red-800';
});

const isBalancedClass = computed(() => {
    return props.health.trial_balance.is_balanced 
        ? 'bg-green-100 text-green-800' 
        : 'bg-red-100 text-red-800';
});

const formatNumber = (value) => {
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(value);
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(value);
};
</script>

<template>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">ACP Accounting — System Health & Ledger Integrity</h1>
            <p class="mt-2 text-gray-600">
                System status and ledger integrity check
            </p>
            <div class="mt-4 flex items-center">
                <span class="text-sm text-gray-500">{{ timestamp }}</span>
                <span 
                    v-if="health.database.connected" 
                    class="ml-3 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ latencyBadgeClass }}"
                    data-testid="db-latency-badge"
                >
                    Operational
                    <span v-if="health.database.latency_ms" class="ml-1">
                        ({{ health.database.latency_ms }}ms)
                    </span>
                </span>
                <span 
                    v-else 
                    class="ml-3 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800"
                    data-testid="db-error-badge"
                >
                    Database Error
                </span>
            </div>
        </div>

        <!-- KPI Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="bg-white shadow rounded-lg p-6" data-testid="accounts-kpi">
                <div class="text-sm font-medium text-gray-500">Chart of Accounts</div>
                <div class="mt-1 text-3xl font-semibold text-gray-900">{{ health.counts.accounts }}</div>
            </div>
            
            <div class="bg-white shadow rounded-lg p-6" data-testid="journal-entries-kpi">
                <div class="text-sm font-medium text-gray-500">Journal Entries</div>
                <div class="mt-1 text-3xl font-semibold text-gray-900">{{ health.counts.journal_entries }}</div>
            </div>
            
            <div class="bg-white shadow rounded-lg p-6" data-testid="bank-accounts-kpi">
                <div class="text-sm font-medium text-gray-500">Bank Accounts</div>
                <div class="mt-1 text-3xl font-semibold text-gray-900">{{ health.counts.bank_accounts }}</div>
            </div>
            
            <div class="bg-white shadow rounded-lg p-6" data-testid="customers-vendors-kpi">
                <div class="text-sm font-medium text-gray-500">Customers & Vendors</div>
                <div class="mt-1 text-3xl font-semibold text-gray-900">{{ health.counts.customers + health.counts.vendors }}</div>
            </div>
        </div>

        <!-- Double-Entry Integrity Panel -->
        <div class="bg-white shadow rounded-lg p-6 mb-8" data-testid="integrity-panel">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Double-Entry Integrity</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="border rounded-lg p-4">
                    <div class="text-sm font-medium text-gray-500">Total Debit</div>
                    <div class="mt-1 text-xl font-semibold text-gray-900">{{ formatCurrency(health.trial_balance.total_debit) }}</div>
                </div>
                
                <div class="border rounded-lg p-4">
                    <div class="text-sm font-medium text-gray-500">Total Credit</div>
                    <div class="mt-1 text-xl font-semibold text-gray-900">{{ formatCurrency(health.trial_balance.total_credit) }}</div>
                </div>
                
                <div class="border rounded-lg p-4">
                    <div class="text-sm font-medium text-gray-500">Difference</div>
                    <div class="mt-1 text-xl font-semibold text-gray-900">{{ formatCurrency(health.trial_balance.difference) }}</div>
                </div>
                
                <div class="border rounded-lg p-4">
                    <div class="text-sm font-medium text-gray-500">Status</div>
                    <div 
                        class="mt-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ isBalancedClass }}"
                        data-testid="balance-status-badge"
                    >
                        {{ health.trial_balance.is_balanced ? 'BALANCED' : 'UNBALANCED' }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Module Diagnostics Table -->
        <div class="bg-white shadow rounded-lg p-6" data-testid="diagnostics-table">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Module Diagnostics</h2>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subsystem</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Record Count</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Database Connection</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span 
                                    v-if="health.database.connected" 
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"
                                >
                                    Connected
                                </span>
                                <span 
                                    v-else 
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800"
                                >
                                    Disconnected
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">-</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Chart of Accounts</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span 
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"
                                >
                                    Present
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ health.counts.accounts }}</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Journal Entries</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span 
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"
                                >
                                    Present
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ health.counts.journal_entries }}</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Customers</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span 
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"
                                >
                                    Present
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ health.counts.customers }}</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Vendors</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span 
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"
                                >
                                    Present
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ health.counts.vendors }}</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Bank Accounts</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span 
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"
                                >
                                    Present
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ health.counts.bank_accounts }}</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Periods</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span 
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"
                                >
                                    Present
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ health.counts.periods }}</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Open Periods</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span 
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                                >
                                    {{ health.periods.open }} open
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ health.periods.open }}</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Closed Periods</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span 
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                                >
                                    {{ health.periods.closed }} closed
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ health.periods.closed }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>