import { createRouter, createWebHistory } from 'vue-router';

const routes = [
    { path: '/login', component: () => import('@/Pages/Auth/Login.vue'), name: 'login' },
    { path: '/forgot-password', component: () => import('@/Pages/Auth/ForgotPassword.vue'), name: 'forgot-password' },
    { path: '/', component: () => import('@/Pages/Dashboard/Home.vue'), name: 'dashboard' },

    // User Management
    { path: '/users', component: () => import('@/Pages/UserManagement/UserList.vue'), name: 'users' },
    { path: '/users/create', component: () => import('@/Pages/UserManagement/CreateUser.vue'), name: 'users.create' },
    { path: '/users/edit/:id', component: () => import('@/Pages/UserManagement/EditUser.vue'), name: 'users.edit' },
    { path: '/users/access-review', component: () => import('@/Pages/UserManagement/AccessReview.vue'), name: 'users.access-review' },

    // Roles & Permissions
    { path: '/roles', component: () => import('@/Pages/UserManagement/RoleList.vue'), name: 'roles' },
    { path: '/roles/matrix', component: () => import('@/Pages/UserManagement/RoleMatrix.vue'), name: 'roles.matrix' },

    // Organization
    { path: '/organization/departments', component: () => import('@/Pages/Organization/DepartmentList.vue'), name: 'organization.departments' },
    { path: '/organization/locations', component: () => import('@/Pages/Organization/LocationList.vue'), name: 'organization.locations' },

    // Employee Management
    { path: '/employees', component: () => import('@/Pages/Employee/EmployeeList.vue'), name: 'employees' },
    { path: '/employees/create', component: () => import('@/Pages/Employee/EmployeeCreate.vue'), name: 'employees.create' },
    { path: '/employees/:id', component: () => import('@/Pages/Employee/EmployeeProfile.vue'), name: 'employees.show' },

    // Attendance
    { path: '/attendance', component: () => import('@/Pages/Employee/Attendance/AttendanceDashboard.vue'), name: 'employee.attendance.hub' },
    { path: '/attendance/timesheets', component: () => import('@/Pages/Employee/Attendance/TimesheetDashboard.vue'), name: 'attendance.timesheets' },
    { path: '/attendance/floating-holidays', component: () => import('@/Pages/Employee/Attendance/FloatingHolidays.vue'), name: 'attendance.floating' },
    { path: '/attendance/swaps', component: () => import('@/Pages/Employee/Attendance/ShiftSwaps.vue'), name: 'attendance.swaps' },

    // Manager Actions
    { path: '/manager/approvals', component: () => import('@/Pages/Manager/Approvals/ApprovalDashboard.vue'), name: 'manager.approvals' },

    // Admin Attendance Config
    { path: '/admin/attendance/monitoring', component: () => import('@/Pages/Admin/Attendance/AttendanceList.vue'), name: 'admin.attendance.monitoring' },
    { path: '/admin/attendance/analytics', component: () => import('@/Pages/Admin/Attendance/AnalyticsDashboard.vue'), name: 'admin.attendance.analytics' },
    { path: '/admin/attendance/roster', component: () => import('@/Pages/Admin/Attendance/ShiftRoster.vue'), name: 'admin.attendance.roster' },

    // Bulk Audits
    { path: '/admin/attendance/timesheets', component: () => import('@/Pages/Admin/Attendance/TimesheetList.vue'), name: 'admin.attendance.timesheets' },
    { path: '/admin/attendance/floating-holidays', component: () => import('@/Pages/Admin/Attendance/FloatingHolidayList.vue'), name: 'admin.attendance.floating-holidays' },
    { path: '/admin/attendance/swaps', component: () => import('@/Pages/Admin/Attendance/ShiftSwapList.vue'), name: 'admin.attendance.swaps' },
    { path: '/admin/attendance/regularization', component: () => import('@/Pages/Admin/Attendance/RegularizationList.vue'), name: 'admin.attendance.regularization' },

    { path: '/admin/attendance/policies', component: () => import('@/Pages/Admin/Attendance/PolicyBuilder.vue'), name: 'admin.policies' },
    { path: '/admin/attendance/workflows', component: () => import('@/Pages/Admin/Attendance/WorkflowBuilder.vue'), name: 'admin.workflows' }, // Needs creation
    { path: '/admin/attendance/gamification', component: () => import('@/Pages/Admin/Gamification/RuleManager.vue'), name: 'admin.gamification' },
    { path: '/admin/attendance/ai-logs', component: () => import('@/Pages/Admin/Ai/LogViewer.vue'), name: 'admin.ai-logs' },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

import { useUiStore } from '@/stores/ui';

router.beforeEach((to, from, next) => {
    const uiStore = useUiStore();
    uiStore.startLoading();
    next();
});

router.afterEach(() => {
    const uiStore = useUiStore();
    uiStore.stopLoading();
});

export default router;
