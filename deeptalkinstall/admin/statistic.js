async function loadStats() {
    const res = await fetch("statistic.php");
    const data = await res.json();

    // === Kasus per Bulan ===
    new Chart(document.getElementById("chartBulan"), {
        type: "bar",
        data: {
            labels: ["Jan","Feb","Mar","Apr","Mei","Jun","Jul","Agu","Sep","Okt","Nov","Des"],
            datasets: [{
                label: "Kasus",
                data: data.bulan,
                backgroundColor: "#00A7E1"
            }]
        }
    });

    // === Kasus per Tahun ===
    new Chart(document.getElementById("chartTahun"), {
        type: "bar",
        data: {
            labels: data.tahun.label.reverse(),
            datasets: [{
                label: "Kasus",
                data: data.tahun.data.reverse(),
                backgroundColor: "#0474BA"
            }]
        }
    });

    // === Kategori Kasus ===
    new Chart(document.getElementById("chartKategori"), {
        type: "pie",
        data: {
            labels: data.kategori.label,
            datasets: [{
                data: data.kategori.data,
                backgroundColor: ["#FF9F1C", "#FF4040", "#2EC4B6", "#E71D36", "#FFD23F"]
            }]
        }
    });

    // === Kasus per Konselor ===
    new Chart(document.getElementById("chartKonselor"), {
        type: "bar",
        data: {
            labels: data.konselor.label,
            datasets: [{
                label: "Kasus",
                data: data.konselor.data,
                backgroundColor: "#00A896"
            }]
        }
    });
}

loadStats();
