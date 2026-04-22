<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="apple-touch-icon" sizes="76x76" href="{{asset('template')}}/assets/img/apple-icon.png">
  <link rel="icon" type="image/png" href="{{asset('template')}}/assets/img/favicon.png">

  <title>@yield('title_page')</title>
  <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,900" />
  <link href="{{asset('template')}}/assets/css/nucleo-icons.css" rel="stylesheet" />
  <link href="{{asset('template')}}/assets/css/nucleo-svg.css" rel="stylesheet" />
  {{-- PERBAIKAN: Ganti kit.fontawesome.com dengan CDN publik dan perbarui integrity hash --}}
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha512-Fo3rlrZj/k7ujTnHg4CGR2D7kSs0v4LLanw2qksYuRlEzO+tcaEPQogQ0KaoGN26/zrn20ImR1DfuLWnOo7aBA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  {{-- BARIS BERIKUT INI HARUS DIHAPUS: --}}
  {{-- <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script> --}}
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
  <link id="pagestyle" href="{{asset('template')}}/assets/css/material-dashboard.css?v=3.2.0" rel="stylesheet" />
</head>

<body class="g-sidenav-show  bg-gray-100">

    @include('layout.v_nav')

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none rounded" id="navbarBlur" data-scroll="true">
          <div class="container-fluid py-2 px-3 d-flex justify-content-between align-items-center">

            <div>
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent mb-1 pb-0 pt-1 px-0">
                  <li class="breadcrumb-item text-sm">
                    <a class="opacity-5 text-dark" href="javascript:;"></a>
                  </li>
                  <li></li>
                </ol>
                <h6 class="font-weight-bold mb-0"></h6>
              </nav>
            </div>

            <ul class="navbar-nav d-flex align-items-center mb-0">

              <li class="nav-item d-xl-none pe-3 d-flex align-items-center">
                <a href="javascript:;" class="nav-link text-body p-0" id="iconNavbarSidenav">
                  <div class="sidenav-toggler-inner">
                    <i class="sidenav-toggler-line"></i>
                    <i class="sidenav-toggler-line"></i>
                    <i class="sidenav-toggler-line"></i>
                  </div>
                </a>
              </li>

              <li class="nav-item d-flex align-items-center">
                <button type="button" class="btn btn-outline-warning d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#profileModal">
                  <i class="material-symbols-rounded me-1" style="color: #fb8c00;">account_circle</i>
                  <span class="fw-semibold text-dark" style="text-transform: capitalize;">
                    {{ ucwords(strtolower(auth()->user()->nama)) }}
                  </span>
                </button>
              </li>
            </ul>
          </div>
        </nav>

        <div class="modal fade" id="profileModal" tabindex="-1" aria-labelledby="profileModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-header border-0">
                <h5 class="modal-title" id="profileModalLabel">Profil Pengguna</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
              </div>
              <div class="modal-body text-center">
                <i class="material-symbols-rounded mb-3" style="font-size: 60px; color: #fb8c00;">account_circle</i>
                <h4 class="fw-bold text-capitalize mb-1">{{ ucwords(strtolower(auth()->user()->nama)) }}</h4>
                <p class="text-muted mb-3">{{ auth()->user()->email }}</p>
                <form method="POST" action="{{ route('logout') }}">
                  @csrf
                  <button type="submit" class="btn btn-danger w-100">Logout</button>
                </form>
              </div>
            </div>
          </div>
        </div>

        @yield('content')

    </main>

        <script src="{{asset('template')}}/assets/js/core/popper.min.js"></script>
  <script src="{{asset('template')}}/assets/js/core/bootstrap.min.js"></script>
  <script src="{{asset('template')}}/assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="{{asset('template')}}/assets/js/plugins/smooth-scrollbar.min.js"></script>
  <script src="{{asset('template')}}/assets/js/plugins/chartjs.min.js"></script>
  <script>
    // PERBAIKAN: Bungkus inisialisasi Chart.js dalam DOMContentLoaded
    // dan tambahkan pengecekan keberadaan elemen canvas.
    document.addEventListener('DOMContentLoaded', function() {
      var ctxBars = document.getElementById("chart-bars");
      if (ctxBars) { // Hanya inisialisasi jika elemen ada
        new Chart(ctxBars.getContext("2d"), {
          type: "bar",
          data: {
            labels: ["M", "T", "W", "T", "F", "S", "S"],
            datasets: [{
              label: "Views",
              tension: 0.4,
              borderWidth: 0,
              borderRadius: 4,
              borderSkipped: false,
              backgroundColor: "#43A047",
              data: [50, 45, 22, 28, 50, 60, 76],
              barThickness: 'flex'
            }, ],
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                display: false,
              }
            },
            interaction: {
              intersect: false,
              mode: 'index',
            },
            scales: {
              y: {
                grid: {
                  drawBorder: false,
                  display: true,
                  drawOnChartArea: true,
                  drawTicks: false,
                  borderDash: [5, 5],
                  color: '#e5e5e5'
                },
                ticks: {
                  suggestedMin: 0,
                  suggestedMax: 500,
                  beginAtZero: true,
                  padding: 10,
                  font: {
                    size: 14,
                    lineHeight: 2
                  },
                  color: "#737373"
                },
              },
              x: {
                grid: {
                  drawBorder: false,
                  display: false,
                  drawOnChartArea: false,
                  drawTicks: false,
                  borderDash: [5, 5]
                },
                ticks: {
                  display: true,
                  color: '#737373',
                  padding: 10,
                  font: {
                    size: 14,
                    lineHeight: 2
                  },
                }
              },
            },
          },
        });
      }

      var ctxLine = document.getElementById("chart-line");
      if (ctxLine) { // Hanya inisialisasi jika elemen ada
        new Chart(ctxLine.getContext("2d"), {
          type: "line",
          data: {
            labels: ["J", "F", "M", "A", "M", "J", "J", "A", "S", "O", "N", "D"],
            datasets: [{
              label: "Sales",
              tension: 0,
              borderWidth: 2,
              pointRadius: 3,
              pointBackgroundColor: "#43A047",
              pointBorderColor: "transparent",
              borderColor: "#43A047",
              backgroundColor: "transparent",
              fill: true,
              data: [120, 230, 130, 440, 250, 360, 270, 180, 90, 300, 310, 220],
              maxBarThickness: 6

            }],
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                display: false,
              },
              tooltip: {
                callbacks: {
                  title: function(context) {
                    const fullMonths = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
                    return fullMonths[context[0].dataIndex];
                  }
                }
              }
            },
            interaction: {
              intersect: false,
              mode: 'index',
            },
            scales: {
              y: {
                grid: {
                  drawBorder: false,
                  display: true,
                  drawOnChartArea: true,
                  drawTicks: false,
                  borderDash: [4, 4],
                  color: '#e5e5e5'
                },
                ticks: {
                  display: true,
                  color: '#737373',
                  padding: 10,
                  font: {
                    size: 12,
                    lineHeight: 2
                  },
                }
              },
              x: {
                grid: {
                  drawBorder: false,
                  display: false,
                  drawOnChartArea: false,
                  drawTicks: false,
                  borderDash: [5, 5]
                },
                ticks: {
                  display: true,
                  color: '#737373',
                  padding: 10,
                  font: {
                    size: 12,
                    lineHeight: 2
                  },
                }
              },
            },
          },
        });
      }

      var ctxTasks = document.getElementById("chart-line-tasks");
      if (ctxTasks) { // Hanya inisialisasi jika elemen ada
        new Chart(ctxTasks.getContext("2d"), {
          type: "line",
          data: {
            labels: ["Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
            datasets: [{
              label: "Tasks",
              tension: 0,
              borderWidth: 2,
              pointRadius: 3,
              pointBackgroundColor: "#43A047",
              pointBorderColor: "transparent",
              borderColor: "#43A047",
              backgroundColor: "transparent",
              fill: true,
              data: [50, 40, 300, 220, 500, 250, 400, 230, 500],
              maxBarThickness: 6

            }],
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                display: false,
              }
            },
            interaction: {
              intersect: false,
              mode: 'index',
            },
            scales: {
              y: {
                grid: {
                  drawBorder: false,
                  display: true,
                  drawOnChartArea: true,
                  drawTicks: false,
                  borderDash: [4, 4],
                  color: '#e5e5e5'
                },
                ticks: {
                  display: true,
                  padding: 10,
                  color: '#737373',
                  font: {
                    size: 14,
                    lineHeight: 2
                  },
                }
              },
              x: {
                grid: {
                  drawBorder: false,
                  display: false,
                  drawOnChartArea: false,
                  drawTicks: false,
                  borderDash: [4, 4]
                },
                ticks: {
                  display: true,
                  color: '#737373',
                  padding: 10,
                  font: {
                    size: 14,
                    lineHeight: 2
                  },
                }
              },
            },
          },
        });
      }
    });
  </script>
  <script>
    var win = navigator.platform.indexOf('Win') > -1;
    if (win && document.querySelector('#sidenav-scrollbar')) {
      var options = {
        damping: '0.5'
      }
      Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
    }
  </script>
  <script async defer src="https://buttons.github.io/buttons.js"></script>
  <script src="{{asset('template')}}/assets/js/material-dashboard.min.js?v=3.2.0"></script>
</body>

</html>
