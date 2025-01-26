<style>
.timeline {
  list-style-type: none;
  padding-left: 0;
  margin: 0;
  text-align: left;
}

.timeline-item {
  display: flex;
  flex-direction: column;
  padding-left: 20px;
  position: relative;
  margin-bottom: 20px;
  border-radius: 4px; /* Optional for smoother look */
}

.timeline-item::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  width: 10px;
  height: 100%;
}

.timeline-time {
  font-weight: bold;
  margin-bottom: 5px;
  color: #2c3e50; /* Adjust to your desired color */
}

.timeline-content {
  padding-left: 20px;
  font-size: 14px;
  color: #7f8c8d; /* Adjust to your desired color */
}

</style>
<div class="timeline">
  @foreach ($approvals as $approval)

    <div class="timeline-item" style="border-left: 10px solid {{ $approval['color'] }}">
      <div class="timeline-time">{{ $approval['status'] }}</div>
      <div class="timeline-content">{{ $approval['status'] }}</div>
    </div>
  @endforeach
</div>

