export default [
  {
    title: "Dashboard",
    to: { name: "root" },
    icon: { icon: "ri-bar-chart-box-line" },
  },
  {
    title: "Usuarios",
    to: { name: "users" },
    icon: { icon: "ri-team-fill" },
    roles: ["ADMIN"],
  },
];
