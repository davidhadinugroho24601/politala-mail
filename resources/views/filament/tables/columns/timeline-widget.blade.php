<style>
  .container {
    max-width: 900px;
    margin: 40px auto;
    text-align: center;
  }

  .timeline {
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
    padding: 20px 0;
  }

  .timeline::before {
    content: "";
    position: absolute;
    top: 50%;
    left: 0;
    width: 100%;
    height: 4px;
    background-color: #dcdcdc;
    z-index: -1;
  }

  .timeline li {
    list-style: none;
    flex: 1;
    text-align: center;
    position: relative;
  }

  .timeline li:before {
    content: "";
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: block;
    margin: auto;
    background-color: currentColor; /* Nodes are fully filled */
    border: 4px solid currentColor; /* Matches border with fill */
    transition: all 0.3s ease-in-out;
  }

  .timeline li .label {
    margin-top: 10px;
    font-size: 14px;
    font-weight: bold;
    color: #2c3e50;
  } 

  .timeline li .status {
    font-size: 12px;
    color: #7f8c8d;
  }

  /* Hover effect */
  .timeline li:hover:before {
    transform: scale(1.1);
    filter: brightness(1.2); /* Slight highlight on hover */
  }
</style>

<div class="container">
  <ul class="timeline">
    @foreach ($getRecord()->approvals ?? [] as $approval)
      <li style="color: {{ $approval['color'] ?? '#3498db' }};">
        <span class="label">{{ $approval['name'] }}</span>
        <br>
        <span class="status">{{ $approval['status'] }}</span>
      </li>
    @endforeach
  </ul>
</div>
