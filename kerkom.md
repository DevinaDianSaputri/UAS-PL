Tata Letak Topologi:

Router0 Gi0/0 ─── Router1 Gi0/0
Router0 Gi0/1     (Tidak terpakai)

Router1 Gi0/2 ─── Router2 Gi0/0
Router1 Gi0/1 ─── Switch-Core Fa0/1 (Trunk)

Switch-Core Fa0/1 (Trunk dari Router1)
    ├── Fa0/2  (PC0 - HRD1)
    ├── Fa0/3  (PC3 - HRD2)
    ├── Fa0/4  (PC1 - KEU1)
    ├── Fa0/5  (PC4 - KEU2)
    ├── Fa0/10 (Server0 - VLAN Admin)
    └── Fa0/24 ─── Switch-L Fa0/1 (Trunk)

Switch-L Fa0/1 (Uplink ke Switch-Core Fa0/24)
    ├── Fa0/2 (PC kiri 1)
    ├── Fa0/3 (PC kiri 2)
    └── Fa0/X (Opsional/IoT)

Switch-R Fa0/3 ─── Switch-Core Fa0/3
    ├── Fa0/2 (PC kanan 1)
    ├── Fa0/0 (PC kanan 2)
    └── Fa0/1 (Access Point)

Access Point Fa0 ─── Switch-R Fa0/1
Laptop0 (Wireless) ─── Access Point

Server0 Fa0 ─── Switch-Core Fa0/10 (VLAN Admin)
IoT Fan (Wireless/Standalone)
