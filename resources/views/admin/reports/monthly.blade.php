@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
  // ====== DATA DARI SERVER ======
  const payload      = @json($charts ? (object)$charts : (object){});
  const trendLabels  = @json($trendLabels ?? []);
  const trendValues  = @json($trendValues ?? []);
  const datasetLabel = @json($trendLabel ?? 'Trend');

  // ====== THEME & UTIL (tanpa hardcode warna) ======
  const isDark  = document.documentElement.classList.contains('dark');
  const gridCol = isDark ? 'rgba(255,255,255,.12)' : 'rgba(0,0,0,.08)';
  const textCol = isDark ? '#e5e7eb' : '#374151';

  Chart.defaults.color = textCol;
  Chart.defaults.font.family = "Inter, ui-sans-serif, system-ui, -apple-system, 'Segoe UI', Roboto, Arial";
  Chart.defaults.borderColor = gridCol;

  // Generator warna algoritmis (golden-angle)
  const hueAt = (i)=> (i*137.508) % 360;
  const hsl = (h,s,l,a=1)=>`hsl(${h} ${s}% ${l}% / ${a})`;
  const dynColor = (i,a=0.95) => {
    const h = hueAt(i);
    const s = isDark ? 60 : 65;
    const l = isDark ? 55 : 45;
    return hsl(h, s, l, a);
  };

  // Formatter angka Indonesia
  const nf0 = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 });
  const nf2 = new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

  const baseOptions = (o={}) => ({
    responsive:true, maintainAspectRatio:false,
    animation:{ duration:800, easing:'easeOutQuart' },
    scales:{
      x:{ grid:{ color:gridCol } },
      y:{ grid:{ color:gridCol }, ticks:{ callback:(v)=> nf0.format(v) } },
    },
    plugins:{
      legend:{ display:false },
      tooltip: {
        backgroundColor: isDark ? 'rgba(17,24,39,.95)' : 'rgba(255,255,255,.95)',
        titleColor: textCol,
        bodyColor: textCol,
        borderColor: gridCol,
        borderWidth: 1,
        callbacks:{
          label(c){
            const ds   = c.dataset;
            const raw  = c.raw ?? 0;
            const unit = (typeof ds.unit === 'function') ? ds.unit(c.dataIndex) : (ds.unit || '');
            const val  = (ds.allInt===true) ? nf0.format(raw) : nf2.format(raw);
            return unit ? `${val} ${unit}` : val;
          }
        }
      }
    },
    ...o
  });

  // Datalabel sederhana (tanpa plugin eksternal)
  const DataLabelPlugin = {
    id: 'valueLabels',
    afterDatasetsDraw(chart) {
      const {ctx, data} = chart;
      data.datasets.forEach((ds, di) => {
        const meta = chart.getDatasetMeta(di); if (!meta || meta.hidden) return;
        meta.data.forEach((el, i) => {
          const v = ds.data[i]; if (v == null) return;
          const unit = (typeof ds.unit === 'function') ? ds.unit(i) : (ds.unit || '');
          const label = (ds.allInt===true ? nf0.format(v) : nf2.format(v)) + (unit ? (' '+unit) : '');
          ctx.save();
          ctx.font = '600 11px ' + Chart.defaults.font.family;
          ctx.fillStyle = textCol;
          const horiz = chart.config.type==='bar' && chart.config.options.indexAxis==='y';
          ctx.textAlign = horiz ? 'left' : 'center';
          ctx.textBaseline = horiz ? 'middle' : 'bottom';
          let x = el.x, y = el.y;
          if (horiz) x = el.x + 8; else y = el.y - 6;
          ctx.fillText(label, x, y);
          ctx.restore();
        });
      });
    }
  };
  Chart.register(DataLabelPlugin);

  // ========== TOP CHART (tanpa campur unit) ==========
  const flat = Object.values(payload || {}).flatMap(g => {
    const units = g.units || [];
    return (g.labels || []).map((label,i)=>({ label, val: (g.values||[])[i] ?? 0, unit: units[i] || '' }));
  });
  const unitCount = flat.reduce((m,r)=>((m[r.unit]=(m[r.unit]||0)+1),m),{});
  const dominantUnit = Object.entries(unitCount).sort((a,b)=>b[1]-a[1])[0]?.[0] ?? '';
  const top = flat.filter(r=>r.unit===dominantUnit).sort((a,b)=>b.val-a.val).slice(0,10);

  if (document.getElementById('topChart') && top.length) {
    new Chart(document.getElementById('topChart'), {
      type:'bar',
      data:{
        labels: top.map(r=>r.label),
        datasets: [{
          data: top.map(r=>r.val),
          backgroundColor: top.map((_,i)=> dynColor(i,.9)),
          borderColor:     top.map((_,i)=> dynColor(i,1)),
          borderWidth: 1,
          borderRadius: 12,
          maxBarThickness: 36,
          allInt: true,
          unit: dominantUnit || ''
        }]
      },
      options: baseOptions({
        scales: {
          x: { grid: { display:false } },
          y: { beginAtZero:true }
        }
      })
    });
  }

  // ========== TREND CHART (pakai data controller, no dummy) ==========
  if (document.getElementById('trendChart') && trendLabels.length) {
    const ctx = document.getElementById('trendChart').getContext('2d');
    const lineColor = dynColor(0, 1);
    const grad = ctx.createLinearGradient(0, 0, 0, ctx.canvas.height);
    grad.addColorStop(0, dynColor(0, .28));
    grad.addColorStop(1, 'hsla(0 0% 0% / 0)');

    const first = trendValues.find(v => v != null) ?? 0;
    const allInt = Number.isInteger(first);

    new Chart(ctx, {
      type:'line',
      data:{
        labels: trendLabels,
        datasets:[{
          data: trendValues,
          label: datasetLabel,
          borderColor: lineColor,
          backgroundColor: grad,
          fill: true,
          tension: .35,
          pointRadius: 3.5,
          pointHoverRadius: 6,
          borderWidth: 2.2,
          allInt: allInt
        }]
      },
      options: baseOptions({
        plugins:{ legend:{display:false} },
        scales:{ x:{ grid:{ display:false } }, y:{ beginAtZero:true } }
      })
    });
  }

  // ========== PER-GROUP (Horizontal bar, unit per bar) ==========
  if (payload && typeof payload === 'object') {
    Object.entries(payload).forEach(([code, cfg]) => {
      const el = document.getElementById('chart_' + code); if (!el) return;
      const n = (cfg.labels || []).length;
      const units = cfg.units || [];
      const allInt = cfg.all_int === true;

      new Chart(el, {
        type:'bar',
        data:{
          labels: cfg.labels || [],
          datasets:[{
            data: cfg.values || [],
            backgroundColor: Array.from({length:n}, (_,i)=> dynColor(i, .92)),
            borderColor:     Array.from({length:n}, (_,i)=> dynColor(i, 1)),
            borderWidth: 1,
            borderRadius: 12,
            barPercentage: .8,
            categoryPercentage: .9,
            allInt: allInt,
            unit: (idx)=> units[idx] || ''
          }]
        },
        options: baseOptions({
          indexAxis:'y',
          scales: {
            x: { beginAtZero:true, grid:{ color: gridCol } },
            y: { grid:{ display:false } }
          }
        })
      });
    });
  }
})();
</script>
@endpush
