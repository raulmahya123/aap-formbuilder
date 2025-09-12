@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
  const payload = @json($charts);

  // ==========
  // THEME + UTIL
  // ==========
  const isDark = document.documentElement.classList.contains('dark');
  const gridColor = isDark ? 'rgba(255,255,255,.12)' : 'rgba(0,0,0,.08)';
  const textColor = isDark ? '#e5e7eb' : '#374151';

  Chart.defaults.color = textColor;
  Chart.defaults.font.family = "Inter, ui-sans-serif, system-ui, -apple-system, 'Segoe UI', Roboto, Arial";
  Chart.defaults.borderColor = gridColor;

  const PALETTE = [
    '#ef4444','#f97316','#f59e0b','#84cc16','#22c55e',
    '#14b8a6','#06b6d4','#0ea5e9','#3b82f6','#6366f1',
    '#8b5cf6','#a78bfa','#e879f9','#f472b6','#fb7185'
  ];

  const rgba = (hex, a=1) => {
    const h = hex.replace('#','');
    const bigint = parseInt(h, 16);
    const r = (bigint >> 16) & 255;
    const g = (bigint >> 8) & 255;
    const b = bigint & 255;
    return `rgba(${r}, ${g}, ${b}, ${a})`;
  };

  const colorsFor = (n) => Array.from({length:n}, (_,i) => PALETTE[i % PALETTE.length]);

  const baseOptions = (overrides={}) => ({
    responsive:true,
    maintainAspectRatio:false,
    animation:{ duration:800, easing:'easeOutQuart' },
    scales:{
      x: { grid: { color: gridColor } },
      y: { grid: { color: gridColor } },
    },
    plugins:{
      legend:{ display:false },
      tooltip: {
        backgroundColor: isDark ? 'rgba(17,24,39,.95)' : 'rgba(255,255,255,.95)',
        titleColor: textColor,
        bodyColor: textColor,
        borderColor: gridColor,
        borderWidth: 1
      }
    },
    ...overrides
  });

  // Simple datalabels (tanpa plugin eksternal)
  const DataLabelPlugin = {
    id: 'valueLabels',
    afterDatasetsDraw(chart, args, pluginOptions) {
      const {ctx} = chart;
      chart.data.datasets.forEach((dataset, dsIndex) => {
        const meta = chart.getDatasetMeta(dsIndex);
        if (!meta || meta.hidden) return;
        meta.data.forEach((el, i) => {
          const val = dataset.data[i];
          if (val == null) return;
          ctx.save();
          ctx.font = '600 11px ' + Chart.defaults.font.family;
          ctx.fillStyle = textColor;
          ctx.textAlign = 'center';
          ctx.textBaseline = 'bottom';
          let x = el.x, y = el.y;
          // geser posisi label sesuai tipe chart / orientasi
          if (chart.config.type === 'bar' && chart.config.options.indexAxis === 'y') {
            ctx.textAlign = 'left';
            ctx.textBaseline = 'middle';
            x = el.x + 8; y = el.y;
          } else {
            y = el.y - 6;
          }
          ctx.fillText(val, x, y);
          ctx.restore();
        });
      });
    }
  };
  Chart.register(DataLabelPlugin);

  // ==========
  // TOP CHART (Bar warna-warni)
  // ==========
  const allRows = Object.values(payload || {}).flatMap(g =>
    (g.labels || []).map((label,i)=>({label, val:g.values?.[i] ?? 0}))
  );
  const top = [...allRows].sort((a,b)=>b.val - a.val).slice(0,10);

  if (document.getElementById('topChart') && top.length) {
    const colors = colorsFor(top.length);
    new Chart(document.getElementById('topChart'), {
      type:'bar',
      data:{
        labels: top.map(r=>r.label),
        datasets: [{
          data: top.map(r=>r.val),
          backgroundColor: colors.map(c => rgba(c, .85)),
          borderColor: colors,
          borderWidth: 1,
          borderRadius: 10,
          maxBarThickness: 36
        }]
      },
      options: baseOptions({
        scales: {
          x: { grid: { display:false } },
          y: { grid: { color: gridColor }, beginAtZero:true }
        }
      })
    });
  }

  // ==========
  // TREND CHART (Line + gradient lembut)
  // ==========
  if (document.getElementById('trendChart')) {
    const ctx = document.getElementById('trendChart').getContext('2d');
    const lineColor = '#3b82f6';
    const grad = ctx.createLinearGradient(0, 0, 0, ctx.canvas.height);
    grad.addColorStop(0, rgba(lineColor, .35));
    grad.addColorStop(1, rgba(lineColor, 0));

    // contoh dummy trend (silakan ganti dengan data asli nanti)
    const labels = Array.from({length: 12}, (_,i)=>`M${i+1}`);
    const values = labels.map(()=> Math.round(Math.random()*100));

    new Chart(ctx, {
      type:'line',
      data:{
        labels,
        datasets:[{
          data: values,
          borderColor: lineColor,
          backgroundColor: grad,
          fill: true,
          tension: .35,
          pointRadius: 3,
          pointHoverRadius: 5,
          borderWidth: 2
        }]
      },
      options: baseOptions({
        plugins:{ legend:{display:false} },
        scales:{
          x:{ grid:{ display:false } },
          y:{ beginAtZero:true }
        }
      })
    });
  }

  // ==========
  // PER-GROUP (Horizontal bar colorful)
  // ==========
  if (payload && typeof payload === 'object') {
    Object.entries(payload).forEach(([code, cfg]) => {
      const el = document.getElementById('chart_' + code);
      if (!el) return;
      const n = (cfg.labels || []).length;
      const colors = colorsFor(n);

      new Chart(el, {
        type:'bar',
        data:{
          labels: cfg.labels || [],
          datasets:[{
            data: cfg.values || [],
            backgroundColor: colors.map(c => rgba(c, .85)),
            borderColor: colors,
            borderWidth: 1,
            borderRadius: 10,
            barPercentage: .8,
            categoryPercentage: .9
          }]
        },
        options: baseOptions({
          indexAxis:'y',
          scales: {
            x: { beginAtZero:true, grid:{ color: gridColor } },
            y: { grid:{ display:false } }
          }
        })
      });
    });
  }
})();
</script>
@endpush
